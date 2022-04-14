<?php

namespace PrestaShop\PrestaShop\Core\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;

final class DealtMissionGridDefinitionFactory extends AbstractGridDefinitionFactory
{
  protected function getId()
  {
    return 'dealt_missions';
  }

  protected function getName()
  {
    return $this->trans('Dealt missions', [], 'Modules.DealtModule.Admin');
  }

  protected function getColumns()
  {
    return (new ColumnCollection())
      ->add((new DataColumn('id_mission'))
          ->setName($this->trans('ID', [], 'Admin.Global'))
          ->setOptions([
            'field' => 'id_product',
          ])
      )
      ->add((new DataColumn('dealt_id_mission'))
          ->setName($this->trans('Mission ID', [], 'Modules.DealtModule.Admin'))
          ->setOptions([
            'field' => 'dealt_id_mission',
          ])
      )
      ->add((new DataColumn('title_mission'))
          ->setName($this->trans('Mission title', [], 'Modules.DealtModule.Admin'))
          ->setOptions([
            'field' => 'title_mission',
          ])
      );
  }
}
