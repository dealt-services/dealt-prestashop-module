<?php

namespace DealtModule\Core\Grid\Filters;

use DealtModule\Core\Grid\Definition\Factory\DealtMissionGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

/**
 * Class DealtMissionFilters is responsible for providing filter values for
 * the DealtMissionGrid
 */
final class DealtMissionFilters extends Filters
{
  /** @var string */
  protected $filterId = DealtMissionGridDefinitionFactory::GRID_ID;

  public static function getDefaults()
  {
    return [
      'limit' => 10,
      'offset' => 0,
      'orderBy' => 'id_mission',
      'sortOrder' => 'asc',
      'filters' => [],
    ];
  }
}
