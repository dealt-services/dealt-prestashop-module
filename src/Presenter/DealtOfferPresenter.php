<?php

declare(strict_types=1);

namespace DealtModule\Presenter;

use Context;
use DealtModule\Entity\DealtOffer;
use DealtModule\Repository\DealtCartProductRefRepository;
use DealtModule\Tools\Helpers;
use Tools;

class DealtOfferPresenter
{
    /** @var DealtCartProductRefRepository */
    private $dealtCartRefRepository;

    /**
     * @param DealtCartProductRefRepository $dealtCartRefRepository
     */
    public function __construct(DealtCartProductRefRepository $dealtCartRefRepository)
    {
        $this->dealtCartRefRepository = $dealtCartRefRepository;
    }

    /**
     * Present dealt offer data for a product
     * id / attribute_id pair
     *
     * @param DealtOffer $offer
     * @param int $productId
     * @param int|null $productAttributeId
     *
     * @return mixed
     */
    public function present(DealtOffer $offer, $productId, $productAttributeId)
    {
        $cart = Context::getContext()->cart;
        $cartProduct = Helpers::getProductFromCart($productId, $productAttributeId);
        $quantity = Tools::getValue('quantity_wanted', (isset($cartProduct['quantity']) ? $cartProduct['quantity'] : null));

        return [
            'offer' => [
                'title' => $offer->getOfferTitle(),
                'description' => $offer->getDealtProduct()->description_short,
                'dealtOfferId' => $offer->getDealtOfferId(),
                'price' => $offer->getFormattedPrice($quantity),
                'unitPrice' => $offer->getPrice(),
                'image' => $offer->getImage(),
                'product' => $offer->getDealtProduct(),
            ],
            'binding' => [
                'productId' => $productId,
                'productAttributeId' => $productAttributeId,
                'cartProduct' => Helpers::getProductFromCart($productId, $productAttributeId),
                'cartOffer' => Helpers::getProductFromCart($offer->getDealtProductId()),
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
