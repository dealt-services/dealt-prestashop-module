<?php

namespace DealtModule\Forms\Admin;

use DealtModule\Entity\DealtMission;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;

class DealtMissionFormDataHandler implements FormDataHandlerInterface
{

  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  /**
   * @param EntityManagerInterface $entityManager
   */
  public function __construct(
    EntityManagerInterface $entityManager
  ) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $data)
  {
    $mission = new DealtMission();
    $mission->setMissionTitle($data['title_mission']);
    $mission->setDealtMissionId($data['dealt_id_mission']);
    $mission->updateTimestamps();

    $this->entityManager->persist($mission);
    $this->entityManager->flush();

    return $mission->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function update($id, array $data)
  {
  }
}
