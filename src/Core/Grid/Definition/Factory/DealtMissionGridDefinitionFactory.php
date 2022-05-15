<?php

namespace DealtModule\Core\Grid\Definition\Factory;

use DealtModule\Core\Grid\Column\DealtMissionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
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
        return $this->trans('Dealt missions by orders', [], 'Modules.Dealtmodule.Admin');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add((new DataColumn('id_order'))
                    ->setName($this->trans('Order ID', [], 'Modules.Dealtmodule.Admin'))
                    ->setOptions([
                        'field' => 'id_order',
                    ])
            )
            ->add((new DealtMissionColumn('missions'))
                    ->setName($this->trans('Missions', [], 'Modules.Dealtmodule.Admin'))
                    ->setOptions(['field' => 'missions'])
            );
    }
}
