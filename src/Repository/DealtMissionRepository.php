<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use DealtModule\Entity\DealtMission;
use DealtModule\Entity\DealtMissionCategory;
use Doctrine\ORM\EntityRepository;
use Exception;
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
   * Updates a Dealt Mission :
   * On each update, for simplicity, we delete every associated
   * DealtMissionCategories linked to the current DealtMission and
   * re-create them from scratch (avoids inconsistencies).
   * Updating the DealtMission::$virtualProductId is optional.
   * 
   * @param int $missionId
   * @param string $missionTitle
   * @param string $dealtMissionId
   * @param int|null $virtualProductId
   * @param array $categoryIds
   * 
   * @return DealtMission
   */
  public function update($missionId, $missionTitle, $dealtMissionId, $virtualProductId, $categoryIds)
  {
    $em = $this->getEntityManager();

    /** @var DealtMission */
    $mission = ($this->findOneById($missionId))
      ->setMissionTitle($missionTitle)
      ->setDealtMissionId($dealtMissionId);

    if ($virtualProductId != null) $mission->setVirtualProductId($virtualProductId);

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
   * Deletes a Dealt Mission and all associated data :
   * - DealtMissionCategories via CASCADE
   * - underlying virtual product
   * 
   * @param int $missionId
   * @return void
   */
  public function delete($missionId)
  {
    $em = $this->getEntityManager();

    /** @var DealtMission */
    $mission = ($this->findOneById($missionId));
    $product = $mission->getVirtualProduct();

    try {
      $product->delete();
    } catch (Exception $_) {
      /* product may have been manually delete */
    }

    $em->remove($mission);
    $em->flush();
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
