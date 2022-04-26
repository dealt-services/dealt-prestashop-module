<?php

namespace DealtModule\Forms\Admin;

use DealtModule\Entity\DealtMission;
use DealtModule\Entity\DealtMissionCategory;
use DealtModule\Repository\DealtMissionRepository;
use DealtModule\Repository\DealtVirtualProductRepository;
use DealtModule\Tools\Helpers;
use Exception;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;

class DealtMissionFormDataProvider implements FormDataProviderInterface
{
  /**
   * @var DealtMissionRepository
   */
  private  $dealtMissionRepository;

  /**
   * @var DealtVirtualProductRepository
   */
  private  $dealtVirtualProductRepository;

  /**
   * @param DealtMissionRepository $dealtMissionRepository
   * @param DealtVirtualProductRepository $dealtVirtualProductRepository
   */
  public function __construct($dealtMissionRepository, $dealtVirtualProductRepository)
  {
    $this->dealtMissionRepository = $dealtMissionRepository;
    $this->dealtVirtualProductRepository = $dealtVirtualProductRepository;
  }
  /**
   * @param int $missionId
   * @return mixed
   */
  public function getData($missionId)
  {
    /** @var DealtMission */
    $mission = $this->dealtMissionRepository->findOneById($missionId);
    $missionData = $mission->toArray();
    $missionData['ids_category'] = $mission->getMissionCategoriesCategoryIds();

    try {
      $product = $this->dealtVirtualProductRepository->findOneById($mission->getVirtualProductId());
      $missionData['mission_price'] = $product->price;
    } catch (Exception $_) { /* associated product may have been deleted */
      $missionData['mission_price'] = Helpers::formatPrice('0.00');
    }

    return $missionData;
  }

  public function getDefaultData()
  {
    return [];
  }
}
