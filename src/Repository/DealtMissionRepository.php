<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use DealtModule\Entity\DealtMission;
use DealtModule\Entity\DealtMissionCategory;
use Doctrine\ORM\EntityRepository;
// use Doctrine\ORM\QueryBuilder;

/**
 * Doctrine DealtMission repository class
 */
class DealtMissionRepository extends EntityRepository
{
  /**
   * Creates a Dealt Mission
   * 
   * @param string $missionTitle
   * @param string $dealtMissionId
   * @param int $virtualProductId
   * @param array $categoryIds
   * 
   * @return DealtMission
   */
  public function create($missionTitle, $dealtMissionId, $virtualProductId, $categoryIds)
  {
    $em = $this->getEntityManager();

    $mission = (new DealtMission())
      ->setMissionTitle($missionTitle)
      ->setDealtMissionId($dealtMissionId)
      ->setVirtualProductId($virtualProductId)
      ->setMissionCategoriesFromIds($categoryIds);

    $em->persist($mission);
    $em->flush();

    return $mission;
  }

  /**
   * Updates a Dealt Mission
   * 
   * @param int $missionId
   * @param string $missionTitle
   * @param string $dealtMissionId
   * @param array $categoryIds
   * 
   * @return DealtMission
   */
  public function update($missionId, $missionTitle, $dealtMissionId, $categoryIds)
  {
    $em = $this->getEntityManager();

    /** @var DealtMission */
    $mission = ($this->findOneById($missionId))
      ->setMissionTitle($missionTitle)
      ->setDealtMissionId($dealtMissionId);

    foreach ($mission->getMissionCategories() as $missionCategory) {
      $this->deleteMissionCategory($missionCategory->getId());
    }

    $mission
      ->clearMissionCategories()
      ->setMissionCategoriesFromIds($categoryIds);

    $em->persist($mission);
    $em->flush();

    return $mission;
  }

  /**
   * Batch delete all categories associated with a dealt mission
   * without flushing the Entity Manager ⚠️
   *
   * @param int $dealtMissionCategoryId
   * @return void
   */
  private function deleteMissionCategory($dealtMissionCategoryId)
  {
    $em = $this->getEntityManager();
    $dealtMissionCategory = $em->getPartialReference(DealtMissionCategory::class, $dealtMissionCategoryId);

    $em->remove($dealtMissionCategory);
  }
}
