<?php

declare(strict_types=1);

namespace DealtModule\Core\Grid\Query;

use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class DealtOfferQueryBuilder extends AbstractDoctrineQueryBuilder
{
  /**
   * @param SearchCriteriaInterface|null $searchCriteria
   * @return QueryBuilder
   */
  public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria = null)
  {
    $qb = $this->getQueryBuilder($searchCriteria->getFilters());
    $qb->select('dm.id_offer, dm.dealt_id_offer, dm.title_offer, dm.id_dealt_product, dmc.id_category')
      ->groupBy('dm.id_offer')
      ->addGroupBy('dmc.id_category')
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
    $qb->select('COUNT(dm.id_offer)');

    return $qb;
  }

  /**
   * @param array $filters
   * @return QueryBuilder
   */
  private function getQueryBuilder(array $filters)
  {
    $allowedFilters = [
      'id_offer',
      'dealt_id_offer',
      'title_offer',
    ];

    $qb = $this->connection
      ->createQueryBuilder()
      ->from($this->dbPrefix . 'dealt_offer', 'dm')
      ->leftJoin('dm', $this->dbPrefix . 'dealt_offer_category', 'dmc', 'dm.id_offer = dmc.id_offer');


    foreach ($filters as $name => $value) {
      if (!in_array($name, $allowedFilters, true)) {
        continue;
      }

      if ('id_offer' === $name || 'dealt_id_offer' === $name) {
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
