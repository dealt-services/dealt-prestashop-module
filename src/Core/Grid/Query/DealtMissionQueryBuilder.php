<?php

declare(strict_types=1);

namespace DealtModule\Core\Grid\Query;

use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class DealtMissionQueryBuilder extends AbstractDoctrineQueryBuilder
{
  /**
   * @param SearchCriteriaInterface|null $searchCriteria
   * @return QueryBuilder
   */
  public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria = null)
  {
    $qb = $this->getQueryBuilder($searchCriteria->getFilters());
    $qb->select('dm.id_mission, dm.dealt_id_mission, dm.title_mission')
      ->groupBy('dm.id_mission')
      ->orderBy(
        $searchCriteria->getOrderBy(),
        $searchCriteria->getOrderWay()
      );

    if ($searchCriteria->getLimit() > 0) {
      $qb
        ->setFirstResult($searchCriteria->getOffset())
        ->setMaxResults($searchCriteria->getLimit());
    }

    return $qb;
  }

  /**
   * @param SearchCriteriaInterface|null $searchCriteria
   * @return QueryBuilder
   */
  public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria = null)
  {
    $qb = $this->getQueryBuilder($searchCriteria->getFilters());
    $qb->select('COUNT(dm.id_mission)');

    return $qb;
  }

  /**
   * @param array $filters
   * @return QueryBuilder
   */
  private function getQueryBuilder(array $filters)
  {
    $allowedFilters = [
      'id_mission',
      'dealt_id_mission',
      'title_mission',
    ];

    $qb = $this->connection
      ->createQueryBuilder()
      ->from($this->dbPrefix . 'dealt_mission', 'dm');

    foreach ($filters as $name => $value) {
      if (!in_array($name, $allowedFilters, true)) {
        continue;
      }

      if ('id_mission' === $name || 'dealt_id_mission' === $name) {
        $qb->andWhere('dm.`' . $name . '` = :' . $name);
        $qb->setParameter($name, $value);

        continue;
      }

      $qb->andWhere("$name LIKE :$name");
      $qb->setParameter($name, '%' . $value . '%');
    }

    return $qb;
  }
}
