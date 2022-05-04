<?php

namespace DealtModule\Core\Grid\Data\Factory;

use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineQueryBuilderInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\QueryParserInterface;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\DependencyInjection\Container;

final class DealtOfferGridDataFactory implements GridDataFactoryInterface
{
    /**
     * @var DoctrineQueryBuilderInterface
     */
    private $gridQueryBuilder;

    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    /**
     * @var QueryParserInterface
     */
    private $queryParser;

    /**
     * @var string
     */
    private $gridId;

    /**
     * @param DoctrineQueryBuilderInterface $gridQueryBuilder
     * @param HookDispatcherInterface $hookDispatcher
     * @param QueryParserInterface $queryParser
     * @param string $gridId
     */
    public function __construct(
    DoctrineQueryBuilderInterface $gridQueryBuilder,
    HookDispatcherInterface $hookDispatcher,
    QueryParserInterface $queryParser,
    $gridId
  ) {
        $this->gridQueryBuilder = $gridQueryBuilder;
        $this->hookDispatcher = $hookDispatcher;
        $this->queryParser = $queryParser;
        $this->gridId = $gridId;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $searchQueryBuilder = $this->gridQueryBuilder->getSearchQueryBuilder($searchCriteria);
        $countQueryBuilder = $this->gridQueryBuilder->getCountQueryBuilder($searchCriteria);

        $this->hookDispatcher->dispatchWithParameters('action' . Container::camelize($this->gridId) . 'GridQueryBuilderModifier', [
      'search_query_builder' => $searchQueryBuilder,
      'count_query_builder' => $countQueryBuilder,
      'search_criteria' => $searchCriteria,
    ]);

        $records = $searchQueryBuilder->execute()->fetchAll();
        $recordsTotal = (int) $countQueryBuilder->execute()->fetch(PDO::FETCH_COLUMN);

        $offersById = [];
        $offerCategoriesById = [];
        foreach ($records as $record) {
            $offerId = (int) $record['id_offer'];
            if (isset($record['id_category'])) {
                $offerCategoriesById[$offerId][] = (int) $record['id_category'];
            }

            $offersById[$offerId] = $record;
        }

        $offers = [];
        foreach ($offersById as $offerId => $offer) {
            $hasCategories = isset($offerCategoriesById[$offerId]);
            $offer['id_categories'] = $hasCategories ? $offerCategoriesById[$offerId] : [];
            $offer['total_categories'] = count($offer['id_categories']);
            unset($offer['id_category']);
            $offers[] = $offer;
        }

        $records = new RecordCollection($offers);

        return new GridData(
      $records,
      $recordsTotal,
      $this->getRawQuery($searchQueryBuilder)
    );
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return string
     */
    private function getRawQuery(QueryBuilder $queryBuilder)
    {
        $query = $queryBuilder->getSQL();
        $parameters = $queryBuilder->getParameters();

        return $this->queryParser->parse($query, $parameters);
    }
}
