<?php

namespace DealtModule\Core\Grid\Filters;

use DealtModule\Core\Grid\Definition\Factory\DealtOfferGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

/**
 * Class DealtOfferFilters is responsible for providing filter values for
 * the DealtOfferGrid
 */
final class DealtOfferFilters extends Filters
{
    /** @var string */
    protected $filterId = DealtOfferGridDefinitionFactory::GRID_ID;

    public static function getDefaults()
    {
        return [
      'limit' => 10,
      'offset' => 0,
      'orderBy' => 'id_offer',
      'sortOrder' => 'asc',
      'filters' => [],
    ];
    }
}
