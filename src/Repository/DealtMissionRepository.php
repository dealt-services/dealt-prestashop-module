<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use DealtModule\Entity\DealtMission;
use DealtModule\Entity\DealtOffer;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine DealtMission repository class
 */
class DealtMissionRepository extends EntityRepository
{
    /**
     * @param int $orderId
     * @param int $productId
     * @param int $productAttributeId
     * @param DealtOffer $offer
     * @param string $dealtMissionId
     * @param float $grossPrice
     * @param float $vatPrice
     * @param float $netPrice
     * @param string $status
     *
     * @return DealtMission
     */
    public function create(
        $orderId,
        $productId,
        $productAttributeId,
        $offer,
        $dealtMissionId,
        $grossPrice,
        $vatPrice,
        $netPrice,
        $status
    ) {
        $em = $this->getEntityManager();

        $mission = (new DealtMission())
            ->setOffer($offer)
            ->setOrderId($orderId)
            ->setProductId($productId)
            ->setProductAttributeId($productAttributeId)
            ->setDealtMissionId($dealtMissionId)
            ->setDealtProductId($offer->getDealtProductId())
            ->setDealtMissionGrossPrice($grossPrice)
            ->setDealtMissionVatPrice($vatPrice)
            ->setDealtMissionNetPrice($netPrice)
            ->setDealtMissionStatus($status);

        $em->persist($mission);
        $em->flush();

        return $mission;
    }

    /**
     * @param string $dealtMissionId
     * @param string $status
     *
     * @return DealtMission
     */
    public function updateStatusByDealtMissionId($dealtMissionId, $status)
    {
        $em = $this->getEntityManager();
        /** @var DealtMission */
        $mission = $this->findOneBy(['dealtMissionId' => $dealtMissionId]);

        $mission->setDealtMissionStatus($status);
        $em->persist($mission);
        $em->flush();

        return $mission;
    }


    /**
     * @param int $orderId
     * @param int $productId
     * @param int $productAttributeId
     * @return DealtMission[]
     */
    public function getMissionsForOrderItem($orderId, $productId, $productAttributeId)
    {
        /** @var DealtMission[] */
        $missions = $this->findBy([
            'orderId' => $orderId,
            'productId' => $productId,
            'productAttributeId' => $productAttributeId
        ]);

        return $missions;
    }
}
