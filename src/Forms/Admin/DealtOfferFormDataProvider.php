<?php

namespace DealtModule\Forms\Admin;

use DealtModule\Entity\DealtOffer;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Repository\DealtProductRepository;
use DealtModule\Tools\Helpers;
use Exception;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;

class DealtOfferFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var DealtOfferRepository
     */
    private $dealtOfferRepository;

    /**
     * @var DealtProductRepository
     */
    private $dealtDealtProductRepository;

    /**
     * @param DealtOfferRepository $dealtOfferRepository
     * @param DealtProductRepository $dealtDealtProductRepository
     */
    public function __construct($dealtOfferRepository, $dealtDealtProductRepository)
    {
        $this->dealtOfferRepository = $dealtOfferRepository;
        $this->dealtDealtProductRepository = $dealtDealtProductRepository;
    }

    /**
     * @param int $offerId
     *
     * @return mixed
     */
    public function getData($offerId)
    {
        /** @var DealtOffer */
        $offer = $this->dealtOfferRepository->findOneById($offerId);
        $offerData = $offer->toArray();
        $offerData['ids_category'] = $offer->getOfferCategoriesCategoryIds();

        try {
            $product = $this->dealtDealtProductRepository->findOneById($offer->getDealtProductId());
            $offerData['offer_price'] = $product->price;
        } catch (Exception $_) { /* associated product may have been deleted */
            $offerData['offer_price'] = Helpers::formatPriceForDB('0.00');
        }

        return $offerData;
    }

    public function getDefaultData()
    {
        return [];
    }
}
