<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use DealtModule\Entity\DealtCartProductRef;
use DealtModule\Entity\DealtOffer;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine DealtCartProductRefRepository repository class
 */
class DealtCartProductRefRepository extends EntityRepository
{
    /**
     * Creates a Dealt Cart Product Offer
     *
     * @param int $cartId
     * @param int $productId
     * @param int $productAttributeId
     * @param DealtOffer $dealtOffer
     *
     * @return DealtCartProductRef
     */
    public function create($cartId, $productId, $productAttributeId, $dealtOffer)
    {
        $em = $this->getEntityManager();

        /* ensure a combination of cartId x productId x dealtProductId does not already exist */
        $match = $this->findOneBy([
            'cartId' => $cartId,
            'productId' => $productId,
            'productAttributeId' => $productAttributeId,
            'offer' => $dealtOffer,
        ]);

        if ($match != null) {
            return $match;
        }

        $dealtCartRef = (new DealtCartProductRef())
            ->setCartId($cartId)
            ->setProductId($productId)
            ->setProductAttributeId($productAttributeId)
            ->setOffer($dealtOffer);

        $em->persist($dealtCartRef);
        $em->flush();

        return $dealtCartRef;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function delete($id)
    {
        $em = $this->getEntityManager();
        $dealtCartRef = $em->getReference(DealtCartProductRef::class, $id);

        $em->remove($dealtCartRef);
        $em->flush();
    }
}
