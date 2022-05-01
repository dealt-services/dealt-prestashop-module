<?php

namespace DealtModule\Forms\Admin;

use DealtModule\Entity\DealtOffer;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Repository\DealtVirtualProductRepository;
use DealtModule\Tools\Helpers;
use Exception;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;

class DealtOfferFormDataProvider implements FormDataProviderInterface
{
  /**
   * @var DealtOfferRepository
   */
  private  $dealtOfferRepository;

  /**
   * @var DealtVirtualProductRepository
   */
  private  $dealtVirtualProductRepository;

  /**
   * @param DealtOfferRepository $dealtOfferRepository
   * @param DealtVirtualProductRepository $dealtVirtualProductRepository
   */
  public function __construct($dealtOfferRepository, $dealtVirtualProductRepository)
  {
    $this->dealtOfferRepository = $dealtOfferRepository;
    $this->dealtVirtualProductRepository = $dealtVirtualProductRepository;
  }
  /**
   * @param int $offerId
   * @return mixed
   */
  public function getData($offerId)
  {
    /** @var DealtOffer */
    $offer = $this->dealtOfferRepository->findOneById($offerId);
    $offerData = $offer->toArray();
    $offerData['ids_category'] = $offer->getOfferCategoriesCategoryIds();

    try {
      $product = $this->dealtVirtualProductRepository->findOneById($offer->getVirtualProductId());
      $offerData['offer_price'] = $product->price;
    } catch (Exception $_) { /* associated product may have been deleted */
      $offerData['offer_price'] = Helpers::formatPrice('0.00');
    }

    return $offerData;
  }

  public function getDefaultData()
  {
    return [];
  }
}
