<?php

declare(strict_types=1);

namespace DealtModule\Service;

use Context;
use Product;
use DealtModule\Presenter\DealtOfferPresenter;
use DealtModule\Repository\DealtOfferRepository;

final class DealtProductService
{
    /** @var DealtOfferRepository */
    private $offerRepository;

    /** @var DealtOfferPresenter */
    private $offerPresenter;

    /**
     * @param DealtOfferRepository $offerRepository
     * @param DealtOfferPresenter $offerPresenter
     */
    public function __construct(
        $offerRepository,
        $offerPresenter
    ) {
        $this->offerRepository = $offerRepository;
        $this->offerPresenter = $offerPresenter;
    }

    /**
     * @param int $productId
     * @param int|null $productAttributeId
     *
     * @return mixed
     */
    public function presentOfferForProduct($productId, $productAttributeId = null)
    {
        $offer = $this->offerRepository->getOfferFromProductCategories($productId);
        $cart = Context::getContext()->cart;
        $product = new Product($productId);

        /* disable dealt services on customizable products */
        return !$product->customizable && $offer != null ?
            $this->offerPresenter->present($offer, $cart, $productId, $productAttributeId)
            : null;
    }
}
