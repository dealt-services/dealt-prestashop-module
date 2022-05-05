<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use Cart;
use DealtModule\Entity\DealtOffer;
use DealtModule\Entity\DealtOfferCategory;
use Doctrine\ORM\EntityRepository;
use Exception;
use Product;

/**
 * Doctrine DealtOffer repository class
 */
class DealtOfferRepository extends EntityRepository
{
    /**
     * Creates a Dealt Offer
     *
     * @param string $offerTitle
     * @param string $dealtOfferId
     * @param int $dealtProductId
     * @param int[] $categoryIds
     *
     * @return DealtOffer
     */
    public function create($offerTitle, $dealtOfferId, $dealtProductId, $categoryIds)
    {
        $em = $this->getEntityManager();

        $offer = (new DealtOffer())
            ->setOfferTitle($offerTitle)
            ->setDealtOfferId($dealtOfferId)
            ->setDealtProductId($dealtProductId)
            ->setOfferCategoriesFromIds($categoryIds);

        $em->persist($offer);
        $em->flush();

        return $offer;
    }

    /**
     * Updates a Dealt Offer :
     * On each update, for simplicity, we delete every associated
     * DealtOfferCategories linked to the current DealtOffer and
     * re-create them from scratch (avoids inconsistencies).
     * Updating the DealtOffer::$dealtProductId is optional.
     *
     * @param int $offerId
     * @param string $offerTitle
     * @param string $dealtOfferId
     * @param int|null $dealtProductId
     * @param int[] $categoryIds
     *
     * @return DealtOffer
     */
    public function update($offerId, $offerTitle, $dealtOfferId, $dealtProductId, $categoryIds)
    {
        $em = $this->getEntityManager();

        /** @var DealtOffer */
        $offer = ($this->findOneBy(['id' => $offerId]))
            ->setOfferTitle($offerTitle)
            ->setDealtOfferId($dealtOfferId);

        if ($dealtProductId != null) {
            $offer->setDealtProductId($dealtProductId);
        }

        foreach ($offer->getOfferCategories() as $offerCategory) {
            $this->deleteOfferCategory($offerCategory->getId());
        }

        $offer
            ->clearOfferCategories()
            ->setOfferCategoriesFromIds($categoryIds);

        $em->persist($offer);
        $em->flush();

        return $offer;
    }

    /**
     * Deletes a Dealt Offer and all associated data :
     * - DealtOfferCategories via CASCADE
     * - underlying virtual product
     *
     * @param int $offerId
     *
     * @return void
     */
    public function delete($offerId)
    {
        $em = $this->getEntityManager();

        /** @var DealtOffer */
        $offer = ($this->findOneBy(['id' => $offerId]));
        $product = $offer->getDealtProduct();

        try {
            $product->delete();
        } catch (Exception $_) {
            /* product may have been manually delete */
        }

        $em->remove($offer);
        $em->flush();
    }

    /**
     * Batch delete all categories associated with a dealt offer
     * without flushing the Entity Manager ⚠️
     *
     * @param int $dealtOfferCategoryId
     *
     * @return void
     */
    private function deleteOfferCategory($dealtOfferCategoryId)
    {
        $em = $this->getEntityManager();
        $dealtOfferCategory = $em->getPartialReference(DealtOfferCategory::class, $dealtOfferCategoryId);

        $em->remove($dealtOfferCategory);
    }

    /**
     * Resolves an offer for a product based on its categories
     *
     * @param int $productId
     *
     * @return DealtOffer|null
     */
    public function getOfferFromProductCategories($productId)
    {
        $product = new Product($productId);
        $categories = $product->getCategories();

        if (empty($categories)) {
            return null;
        }
        /**
         * Find only first match - we may have multiple results
         * but this can only be caused either by :
         * - a category conflict due to a misconfiguration
         * - matching a parent/child category
         */
        $em = $this->getEntityManager();

        /** @var DealtOfferCategoryRepository */
        $offerCategoryRepository = $em->getRepository(DealtOfferCategory::class);
        /** @var DealtOfferCategory|null */
        $offerCategory = $offerCategoryRepository->findOneBy(['categoryId' => $categories]);

        if ($offerCategory == null) {
            return null;
        }

        return $offerCategory->getOffer();
    }

    /**
     * Resolves the dealt offers from the current
     * cart products.
     *
     * @param Cart $cart
     *
     * @return DealtOffer[]
     */
    public function getDealtOffersFromCart(Cart $cart)
    {
        try {
            $cartProducts = $cart->getProducts();
            $cartProductIds = array_map(function ($cartProduct) {
                return (int) $cartProduct['id_product'];
            }, $cartProducts);

            return $this->findBy(['dealtProductId' => $cartProductIds]);
        } catch (Exception $e) {
            /* cart may not exist yet in DB and will make internal cart methods crash */
            return [];
        }
    }
}
