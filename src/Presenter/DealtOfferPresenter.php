<?php

declare(strict_types=1);

namespace DealtModule\Presenter;

use Cart;
use DealtModule\Entity\DealtOffer;
use DealtModule\Repository\DealtCartProductRefRepository;
use DealtModule\Repository\DealtMissionRepository;
use DealtModule\Tools\Helpers;
use Tools;

class DealtOfferPresenter
{
    /** @var DealtCartProductRefRepository */
    private $dealtCartRefRepository;

    /** @var DealtMissionRepository */
    private $missionRepository;

    /**
     * @param DealtCartProductRefRepository $dealtCartRefRepository
     */
    public function __construct(DealtCartProductRefRepository $dealtCartRefRepository, DealtMissionRepository $missionRepository)
    {
        $this->dealtCartRefRepository = $dealtCartRefRepository;
        $this->missionRepository = $missionRepository;
    }

    /**
     * Present dealt offer data for a product
     * id / attribute_id pair
     *
     * @param DealtOffer $offer
     * @param Cart $cart
     * @param int $productId
     * @param int|null $productAttributeId
     * @param int|null $orderId
     *
     * @return mixed
     */
    public function present(DealtOffer $offer, Cart $cart, $productId, $productAttributeId, $orderId = null)
    {
        $cartProduct = Helpers::getProductFromCart($cart, $productId, $productAttributeId);
        $quantity = Tools::getValue('quantity_wanted', isset($cartProduct['cart_quantity']) ? $cartProduct['cart_quantity'] : null);

        return [
            'offer' => array_merge([
                'title' => $offer->getOfferTitle(),
                'description' => $offer->getDealtProduct()->description_short,
                'dealtOfferId' => $offer->getDealtOfferId(),
                'price' => $offer->getFormattedPrice($quantity),
                'unitPriceFormatted' => $offer->getFormattedPrice(),
                'unitPrice' => $offer->getPrice(),
                'image' => $offer->getImage(),
                'product' => $offer->getDealtProduct(),
            ], $orderId != null ? [
                'missions' => $this->missionRepository->getMissionsForOrderItem($orderId, $productId, $productAttributeId),
            ] : []),
            'binding' => [
                'productId' => $productId,
                'productAttributeId' => $productAttributeId,
                'cartProduct' => $cartProduct,
                'cartOffer' => Helpers::getProductFromCart($cart, $offer->getDealtProductId(), null),
                'data' => array_merge(
                    [
                        'cartId' => $cart->id,
                        'productId' => $productId,
                        'offer' => $offer,
                    ],
                    $productAttributeId != null ? ['productAttributeId' => $productAttributeId] : []
                ),
                'cartRef' => $this->dealtCartRefRepository->findOneBy(
                    array_merge(
                        [
                            'cartId' => $cart->id,
                            'productId' => $productId,
                            'offer' => $offer,
                        ],
                        $productAttributeId != null ? ['productAttributeId' => $productAttributeId] : []
                    )
                ),
            ],
        ];
    }
}
