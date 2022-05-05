<?php

declare(strict_types=1);

namespace DealtModule\Service;

use Address;
use Cart;
use DealtModule\Entity\DealtCartProductRef;
use DealtModule\Repository\DealtCartProductRefRepository;
use DealtModule\Repository\DealtMissionRepository;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Tools\Helpers;
use Order;

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
        DealtCartProductRefRepository $dealtCartRefRepository
    ) {
        $this->apiService = $apiService;
        $this->missionRepository = $missionRepository;
        $this->offerRepository = $offerRepository;
        $this->dealtCartRefRepository = $dealtCartRefRepository;
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
                $quantity = $subResult['cartProduct']['quantity'];
                $productId = $subResult['cartProduct']['id_product'];
                $productAttributeId = $subResult['cartProduct']['id_product_attribute'];
                $offer = $subResult['offer'];
                $offerAvailability = $subResult['offerAvailability'];
                $checked = $offerAvailability != null;

                $available = $checked ? $offerAvailability->available : false;
                $vatPrice = $checked ? $offerAvailability->vat_price->amount : 0;
                $grossPrice = $checked ? $offerAvailability->gross_price->amount : 0;
                $netPrice = $checked ? $offerAvailability->net_price->amount : 0;

                $match = $this->missionRepository->findOneBy([
                    'orderId' => $orderId,
                    'productId' => $productId,
                    'productAttributeId' => $productAttributeId,
                    'offer' => $offer,
                ]);

                /* make sure missions have not already been submitted */
                if ($match == null) {
                    foreach (range(1, $quantity) as $_) {
                        $mission = $available ? $this->apiService->submitMission($offer, $order) : null;
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
}
