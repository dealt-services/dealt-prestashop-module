<?php

namespace DealtModule\Forms\Admin;

use DealtModule\Entity\DealtOffer;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Repository\DealtProductRepository;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductNotFoundException;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;

class DealtOfferFormDataHandler implements FormDataHandlerInterface
{
    /**
     * @var DealtOfferRepository
     */
    private $offerRepository;

    /**
     * @var DealtProductRepository
     */
    private $productRepository;

    /**
     * @param DealtOfferRepository $offerRepository
     * @param DealtProductRepository $productRepository
     */
    public function __construct(
        $offerRepository,
        $productRepository
    ) {
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
        $offer = $this->offerRepository->findOneBy(['id' => $offerId]);

        try {
            $this->productRepository->update($offer->getDealtProductId(), $offerTitle, $offerPrice);
            $this->offerRepository->update($offerId, $offerTitle, $dealtOfferId, null, $categoryIds);
        } catch (ProductNotFoundException $e) {
            $product = $this->productRepository->create($offerTitle, $dealtOfferId, $offerPrice);
            $this->offerRepository->update($offerId, $offerTitle, $dealtOfferId, $product->id, $categoryIds);
        }
    }
}
