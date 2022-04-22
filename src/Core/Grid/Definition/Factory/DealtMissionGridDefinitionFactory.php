<?php

namespace DealtModule\Core\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\SubmitRowAction;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractFilterableGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\LinkColumn;

final class DealtMissionGridDefinitionFactory extends AbstractFilterableGridDefinitionFactory
{
  const GRID_ID = 'dealt_missions';

  protected function getId()
  {
    return self::GRID_ID;
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
            'field' => 'id_mission',
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
      )
      ->add((new LinkColumn('product_link'))
          ->setName($this->trans('Virtual Dealt Product', [], 'Modules.DealtModule.Admin'))
          ->setOptions([
            'icon' => 'edit',
            'target' => '_BLANK',
            'field' => 'id_dealt_virtual_product',
            'route' => 'admin_product_form',
            'route_param_name' => 'id',
            'route_param_field' => 'id_dealt_virtual_product',
          ])
      )
      ->add((new ActionColumn('actions'))
          ->setName($this->trans('Actions', [], 'Admin.Global'))
          ->setOptions([
            'actions' => (new RowActionCollection())
              ->add((new LinkRowAction('edit'))
                  ->setName($this->trans('Edit', [], 'Admin.Actions'))
                  ->setIcon('edit')
                  ->setOptions([
                    'route' => 'admin_dealt_missions_edit',
                    'route_param_name' => 'missionId',
                    'route_param_field' => 'id_mission',
                  ])
              )
              ->add((new SubmitRowAction('delete'))
                  ->setName($this->trans('Delete', [], 'Admin.Actions'))
                  ->setIcon('delete')
                  ->setOptions([
                    'method' => 'DELETE',
                    'route' => 'admin_dealt_missions_delete',
                    'route_param_name' => 'missionId',
                    'route_param_field' => 'id_mission',
                    'confirm_message' => $this->trans(
                      'Delete selected item?',
                      [],
                      'Admin.Notifications.Warning'
                    ),
                  ])
              )
          ])
      );
  }
}
