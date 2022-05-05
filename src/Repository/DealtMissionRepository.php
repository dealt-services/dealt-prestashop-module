<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use Dealt\DealtSDK\GraphQL\Types\Object\OfferAvailabilityQuerySuccess;
use DealtModule\Entity\DealtMission;
use DealtModule\Entity\DealtOffer;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine DealtMission repository class
 */
class DealtMissionRepository extends EntityRepository
{
    /**
     * @param int $productId
     * @param int $productAttributeId
     * @param DealtOffer $offer
     * @param OfferAvailabilityQuerySuccess $dealtOffer
     * @param string $status
     * 
     * @return DealtMission
     */
    public function create($productId, $productAttributeId, DealtOffer $offer, OfferAvailabilityQuerySuccess $dealtOffer, $status)
    {
        $em = $this->getEntityManager();

        $mission = (new DealtMission())
            ->setOffer($offer)
            ->setProductId($productId)
            ->setProductAttributeId($productAttributeId)
            ->setDealtMissionId($offer->getDealtOfferId())
            ->setDealtProductId($offer->getDealtProductId())
            ->setDealtMissionGrossPrice($dealtOffer->gross_price->amount)
            ->setDealtMissionVatPrice($dealtOffer->vat_price->amount)
            ->setDealtMissionNetPrice($dealtOffer->net_price->amount)
            ->setDealtMissionStatus($status);

        $em->persist($mission);
        $em->flush();

        return $mission;
    }
}
