<?php

declare(strict_types=1);

namespace DealtModule\Service;

use Address;
use Cart;
use DealtModule\Entity\DealtCartProductRef;
use DealtModule\Entity\DealtMission;
use DealtModule\Presenter\DealtOfferPresenter;
use DealtModule\Repository\DealtCartProductRefRepository;
use DealtModule\Repository\DealtMissionRepository;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Tools\Helpers;
use Order;
use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderLazyArray;
use Product;

final class DealtOrderService
{
    /** @var DealtAPIService */
    private $apiService;

    /** @var DealtMissionRepository */
    private $missionRepository;

    /** @var DealtOfferRepository */
    private $offerRepository;

    /** @var DealtCartProductRefRepository */
    private $dealtCartRefRepository;

    /** @var DealtOfferPresenter */
    private $offerPresenter;

    /**
     * @param DealtAPIService $apiService
     * @param DealtMissionRepository $missionRepository
     * @param DealtOfferRepository $offerRepository
     * @param DealtCartProductRefRepository $dealtCartRefRepository
     */
    public function __construct(
        DealtAPIService $apiService,
        DealtMissionRepository $missionRepository,
        DealtOfferRepository $offerRepository,
        DealtCartProductRefRepository $dealtCartRefRepository,
        DealtOfferPresenter $offerPresenter
    ) {
        $this->apiService = $apiService;
        $this->missionRepository = $missionRepository;
        $this->offerRepository = $offerRepository;
        $this->dealtCartRefRepository = $dealtCartRefRepository;
        $this->offerPresenter = $offerPresenter;
    }

    /**
     * This hook function will create every dealt
     * offer associated to an order. Depending on the number of
     * products / services this may take a while..
     * TODO in the future : use some queuing mechanism to ensure its
     * non-blocking for the user (front or back office)
     *
     * @param int $orderId
     *
     * @return void
     */
    public function handleOrderPayment($orderId)
    {
        $order = new Order($orderId);
        $cartId = Order::getCartIdStatic($order->id, $order->id_customer);
        $cart = new Cart($cartId);

        $deliveryAddress = new Address($order->id_address_delivery);
        $zipCode = $deliveryAddress->postcode;
        $country = $deliveryAddress->country;

        $offers = $this->offerRepository->getDealtOffersFromCart($cart);

        $results = [];
        /*
         * Iterate over every offer in the current order cart
         * and construct the necessary data for communicating
         * with the Dealt API
         */
        foreach ($offers as $offer) {
            $results[$offer->getDealtOfferId()] = array_map(function (DealtCartProductRef $ref) use ($offer, $cart, $zipCode, $country) {
                return [
                    'ref' => $ref,
                    'cartProduct' => Helpers::getProductFromCart(
                        $cart,
                        $ref->getProductId(),
                        $ref->getProductAttributeId()
                    ),
                    'offerAvailability' => $this->apiService->checkAvailability(
                        $offer->getDealtOfferId(),
                        $zipCode,
                        $country
                    ),
                    'offer' => $offer,
                ];
            }, $this->dealtCartRefRepository->findBy([
                'cartId' => $cartId,
                'offer' => $offer,
            ]));
        }

        /*
         * Create a single mission entry per offer and per product
         * quantities. If we fail to submit a mission for any reason,
         * create the DealtMission entry either way : it will be flagged.
         */
        foreach ($results as $_ => $subResults) {
            foreach ($subResults as $subResult) {
                $quantity = $subResult['cartProduct']['cart_quantity'];
                $productId = $subResult['cartProduct']['id_product'];
                $productAttributeId = $subResult['cartProduct']['id_product_attribute'];
                $offer = $subResult['offer'];
                $offerAvailability = $subResult['offerAvailability'];
                $checked = $offerAvailability != null;

                $available = $checked ? $offerAvailability->available : false;
                $vatPrice = $checked ? $offerAvailability->vat_price->amount : 0;
                $grossPrice = $checked ? $offerAvailability->gross_price->amount : 0;
                $netPrice = $checked ? $offerAvailability->net_price->amount : 0;

                /** @var DealtMission|null */
                $match = $this->missionRepository->findOneBy([
                    'orderId' => $orderId,
                    'productId' => $productId,
                    'productAttributeId' => $productAttributeId,
                    'offer' => $offer,
                ]);

                $product = new Product($productId);

                /* make sure missions have not already been submitted */
                if ($match == null) {
                    foreach (range(1, $quantity) as $_) {
                        $mission = $available ? $this->apiService->submitMission($offer, $order, $product) : null;
                        $status = $mission != null ? $mission->status : 'ERROR_UNABLE_TO_SUBMIT';
                        $dealtMissionId = $mission != null ? $mission->id : '-';

                        $this->missionRepository->create(
                            $orderId,
                            $productId,
                            $productAttributeId,
                            $offer,
                            $dealtMissionId,
                            $vatPrice,
                            $grossPrice,
                            $netPrice,
                            $status
                        );
                    }
                }
            }
        }
    }

    /**
     * Filters in place the presented offer data
     * adds dealt specific data to products attached to a dealt offer
     *
     * @param OrderLazyArray $presentedOffer
     *
     * @return void
     */
    public function sanitizeOrderPresenter(OrderLazyArray &$presentedOffer)
    {
        $orderRef = $presentedOffer['history']['current'];
        $orderId = intval($orderRef['id_order']);
        $order = new Order($orderId);

        $cartId = Order::getCartIdStatic($order->id, $order->id_customer);
        $cart = new Cart($cartId);

        /** @var DealtCartProductRef[] */
        $dealtCartRefs = $this->dealtCartRefRepository->findBy(['cartId' => $cart->id]);
        $presentedOffer->offsetSet('hasDealtServices', !empty($dealtCartRefs), true);

        $dealtCartDealtProductIds = array_map(function (DealtCartProductRef $dealtCartRef) {
            return $dealtCartRef
                ->getOffer()
                ->getDealtProductId();
        }, $dealtCartRefs);

        $offerProducts = array_filter($presentedOffer['products'], function ($product) use ($dealtCartDealtProductIds) {
            return !in_array(
                $product['id_product'],
                $dealtCartDealtProductIds
            );
        });

        foreach ($offerProducts as &$offerProduct) {
            foreach ($dealtCartRefs as $dealtCartRef) {
                if (
                    $dealtCartRef->getProductId() == $offerProduct['id_product'] &&
                    $dealtCartRef->getProductAttributeId() == $offerProduct['id_product_attribute']
                ) {
                    $offer = $dealtCartRef->getOffer();

                    $offerProduct['dealt'] = $this->offerPresenter->present(
                        $offer,
                        $cart,
                        $offerProduct['id_product'],
                        $offerProduct['id_product_attribute'],
                        $orderId
                    );
                }
            }
        }

        $presentedOffer->offsetSet('products', $offerProducts, true);
    }
}
