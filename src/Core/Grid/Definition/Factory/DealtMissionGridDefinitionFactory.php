<?php

namespace DealtModule\Core\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\LinkColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractFilterableGridDefinitionFactory;

final class DealtMissionGridDefinitionFactory extends AbstractFilterableGridDefinitionFactory
{
  public const GRID_ID = 'dealt_mission';

  protected function getId()
  {
    return self::GRID_ID;
  }

  protected function getName()
  {
    return $this->trans('Dealt mission', [], 'Modules.DealtModule.Admin');
  }

  protected function getColumns()
  {
    return (new ColumnCollection())
      ->add((new DataColumn('id_mission'))
          ->setName($this->trans('ID', [], 'Admin.Global'))
          ->setOptions([
            'field' => 'id_mission',
          ])
      )
      ->add((new DataColumn('id_order'))
          ->setName($this->trans('Order ID', [], 'Admin.Global'))
          ->setOptions([
            'field' => 'id_order',
          ])
      )
      ->add((new DataColumn('id_offer'))
          ->setName($this->trans('Offer ID', [], 'Admin.Global'))
          ->setOptions([
            'field' => 'id_offer',
          ])
      )
      ->add((new DataColumn('dealt_id_mission'))
          ->setName($this->trans('Mission ID', [], 'Modules.DealtModule.Admin'))
          ->setOptions([
            'field' => 'dealt_id_mission',
          ])
      );
  }
}
