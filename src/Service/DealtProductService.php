<?php

declare(strict_types=1);

namespace DealtModule\Service;

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

        return $offer != null ?
            $this->offerPresenter->present($offer, $productId, $productAttributeId)
            : null;
    }
}
