<?php

namespace DealtModule\Forms\Admin;

use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;

class DealtMissionFormDataProvider implements FormDataProviderInterface
{
  /**
   * @return mixed
   */
  public function getData($_)
  {
    return [];
  }

  public function getDefaultData()
  {
    return [];
  }
}
