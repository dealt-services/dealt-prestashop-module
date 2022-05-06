<?php

declare(strict_types=1);

namespace DealtModule\Core\Grid\Column;

use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DealtMissionColumn extends AbstractColumn
{
    public function __construct($id)
    {
        parent::__construct($id);
    }

    public function getType()
    {
        return 'dealtmodule_mission_column';
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['field'])
            ->setAllowedTypes('field', 'string');
    }
}
