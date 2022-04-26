<?php

namespace DealtModule\Forms\Admin;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;
use DealtModule\Repository\DealtMissionRepository;
use DealtModule\Repository\DealtVirtualProductRepository;
use DealtModule\Entity\DealtMission;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductNotFoundException;

class DealtMissionFormDataHandler implements FormDataHandlerInterface
{

  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  /**
   * @var DealtMissionRepository
   */
  private  $missionRepository;

  /**
   * @var DealtVirtualProductRepository
   */
  private  $productRepository;

  /**
   * @param EntityManagerInterface $entityManager
   * @param DealtMissionRepository $missionRepository
   * @param DealtVirtualProductRepository $productRepository
   */
  public function __construct(
    $entityManager,
    $missionRepository,
    $productRepository
  ) {
    $this->entityManager = $entityManager;
    $this->missionRepository = $missionRepository;
    $this->productRepository = $productRepository;
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $data)
  {
    $missionTitle = $data['title_mission'];
    $dealtMissionId = $data['dealt_id_mission'];
    $missionPrice = $data['mission_price'];
    $categoryIds = isset($data['ids_category']) ? $data['ids_category'] : [];

    $product = $this->productRepository->create($missionTitle, $dealtMissionId, $missionPrice);
    $mission = $this->missionRepository->create($missionTitle, $dealtMissionId, $product->id, $categoryIds);

    return $mission->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function update($missionId, array $data)
  {
    $missionTitle = $data['title_mission'];
    $dealtMissionId = $data['dealt_id_mission'];
    $missionPrice = $data['mission_price'];
    $categoryIds = isset($data['ids_category']) ? $data['ids_category'] : [];

    /** @var DealtMission */
    $mission = $this->missionRepository->findOneById($missionId);

    try {
      $this->productRepository->update($mission->getVirtualProductId(), $missionTitle, $missionPrice);
      $this->missionRepository->update($missionId, $missionTitle, $dealtMissionId, null, $categoryIds);
    } catch (ProductNotFoundException $_) {
      $product = $this->productRepository->create($missionTitle, $dealtMissionId, $missionPrice);
      $this->missionRepository->update($missionId, $missionTitle, $dealtMissionId, $product->id, $categoryIds);
    }
  }
}
