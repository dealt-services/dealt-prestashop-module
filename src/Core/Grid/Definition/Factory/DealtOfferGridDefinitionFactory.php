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

final class DealtOfferGridDefinitionFactory extends AbstractFilterableGridDefinitionFactory
{
    public const GRID_ID = 'dealt_offer';

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Dealt offer', [], 'Modules.Dealtmodule.Admin');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
      ->add((new DataColumn('id_offer'))
          ->setName($this->trans('ID', [], 'Admin.Global'))
          ->setOptions([
            'field' => 'id_offer',
          ])
      )
      ->add((new DataColumn('dealt_id_offer'))
          ->setName($this->trans('Offer ID', [], 'Modules.Dealtmodule.Admin'))
          ->setOptions([
            'field' => 'dealt_id_offer',
          ])
      )
      ->add((new DataColumn('title_offer'))
          ->setName($this->trans('Offer title', [], 'Modules.Dealtmodule.Admin'))
          ->setOptions([
            'field' => 'title_offer',
          ])
      )
      ->add((new DataColumn('total_categories'))
          ->setName($this->trans('Total categories', [], 'Modules.Dealtmodule.Admin'))
          ->setOptions([
            'field' => 'total_categories',
          ])
      )
      ->add((new LinkColumn('product_link'))
          ->setName($this->trans('Virtual Dealt Product', [], 'Modules.Dealtmodule.Admin'))
          ->setOptions([
            'icon' => 'link',
            'target' => '_BLANK',
            'field' => 'id_dealt_product',
            'route' => 'admin_product_form',
            'route_param_name' => 'id',
            'route_param_field' => 'id_dealt_product',
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
                    'route' => 'admin_dealt_offer_edit',
                    'route_param_name' => 'offerId',
                    'route_param_field' => 'id_offer',
                  ])
              )
              ->add((new SubmitRowAction('delete'))
                  ->setName($this->trans('Delete', [], 'Admin.Actions'))
                  ->setIcon('delete')
                  ->setOptions([
                    'method' => 'POST',
                    'route' => 'admin_dealt_offer_delete',
                    'route_param_name' => 'offerId',
                    'route_param_field' => 'id_offer',
                    'confirm_message' => $this->trans(
                        'Are you sure you want to delete this Dealt Offer ? All associated data will be forever lost (dealt product, linked categories etc..)',
                        [],
                        'Modules.Dealtmodule.Admin'
                    ),
                  ])
              ),
          ])
      );
    }
}
