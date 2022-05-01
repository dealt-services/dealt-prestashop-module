<?php

namespace DealtModule\Forms\Admin;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Repository\DealtProductRepository;
use DealtModule\Entity\DealtOffer;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductNotFoundException;

class DealtOfferFormDataHandler implements FormDataHandlerInterface
{

  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  /**
   * @var DealtOfferRepository
   */
  private  $offerRepository;

  /**
   * @var DealtProductRepository
   */
  private  $productRepository;

  /**
   * @param EntityManagerInterface $entityManager
   * @param DealtOfferRepository $offerRepository
   * @param DealtProductRepository $productRepository
   */
  public function __construct(
    $entityManager,
    $offerRepository,
    $productRepository
  ) {
    $this->entityManager = $entityManager;
    $this->offerRepository = $offerRepository;
    $this->productRepository = $productRepository;
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $data)
  {
    $offerTitle = $data['title_offer'];
    $dealtOfferId = $data['dealt_id_offer'];
    $offerPrice = $data['offer_price'];
    $categoryIds = isset($data['ids_category']) ? $data['ids_category'] : [];

    $product = $this->productRepository->create($offerTitle, $dealtOfferId, $offerPrice);
    $offer = $this->offerRepository->create($offerTitle, $dealtOfferId, $product->id, $categoryIds);

    return $offer->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function update($offerId, array $data)
  {
    $offerTitle = $data['title_offer'];
    $dealtOfferId = $data['dealt_id_offer'];
    $offerPrice = $data['offer_price'];
    $categoryIds = isset($data['ids_category']) ? $data['ids_category'] : [];

    /** @var DealtOffer */
    $offer = $this->offerRepository->findOneById($offerId);

    try {
      $this->productRepository->update($offer->getDealtProductId(), $offerTitle, $offerPrice);
      $this->offerRepository->update($offerId, $offerTitle, $dealtOfferId, null, $categoryIds);
    } catch (ProductNotFoundException $_) {
      $product = $this->productRepository->create($offerTitle, $dealtOfferId, $offerPrice);
      $this->offerRepository->update($offerId, $offerTitle, $dealtOfferId, $product->id, $categoryIds);
    }
  }
}
