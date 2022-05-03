<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use DealtModule\Entity\DealtCartProduct;
use DealtModule\Entity\DealtOffer;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine DealtOfferCategory repository class
 */
class DealtCartProductRepository extends EntityRepository
{
  /**
   * Creates a Dealt Cart Product Offer
   * 
   * @param int $cartId
   * @param int $productId
   * @param int $productAttributeId
   * @param DealtOffer $dealtOffer
   * 
   * @return DealtCartProduct
   */
  public function create($cartId, $productId, $productAttributeId, $dealtOffer)
  {
    $em = $this->getEntityManager();

    /* ensure a combination of cartId x productId x dealtProductId does not already exist */
    $match = $this->findOneBy([
      'cartId' => $cartId,
      'productId' => $productId,
      'productAttributeId' => $productAttributeId,
      'offer' => $dealtOffer
    ]);

    if ($match != null) return $match;

    $cartProductOffer = (new DealtCartProduct())
      ->setCartId($cartId)
      ->setProductId($productId)
      ->setProductAttributeId($productAttributeId)
      ->setOffer($dealtOffer);

    $em->persist($cartProductOffer);
    $em->flush();

    return $cartProductOffer;
  }

  /**
   * @param int $id
   * @return void
   */
  public function delete($id)
  {
    $em = $this->getEntityManager();
    $cartProduct = $em->getReference(DealtCartProduct::class, $id);

    $em->remove($cartProduct);
    $em->flush();
  }
}
