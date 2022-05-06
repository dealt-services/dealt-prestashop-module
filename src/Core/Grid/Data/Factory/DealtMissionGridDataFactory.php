<?php

namespace DealtModule\Core\Grid\Data\Factory;

use Context;
use DealtModule\Entity\DealtOffer;
use DealtModule\Repository\DealtOfferRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Product;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineQueryBuilderInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\QueryParserInterface;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\DependencyInjection\Container;

final class DealtMissionGridDataFactory implements GridDataFactoryInterface
{
    /**
     * @var DealtOfferRepository
     */
    private $offerRepository;

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
     * @param DealtOfferRepository $offerRepository
     * @param DoctrineQueryBuilderInterface $gridQueryBuilder
     * @param HookDispatcherInterface $hookDispatcher
     * @param QueryParserInterface $queryParser
     * @param string $gridId
     */
    public function __construct(
        DealtOfferRepository $offerRepository,
        DoctrineQueryBuilderInterface $gridQueryBuilder,
        HookDispatcherInterface $hookDispatcher,
        QueryParserInterface $queryParser,
        $gridId
    ) {
        $this->offerRepository = $offerRepository;
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

        $offerIds = array_unique(array_map(function ($record) {
            return intval($record['id_offer']);
        }, $records));

        $offers = [];
        foreach ($this->offerRepository->findBy(["id" => $offerIds]) as $offer) {
            $offers[$offer->getId()] = $offer->toArray();
        }


        $missionsByOrderId = [];

        foreach ($records as $record) {
            $orderId = (int) $record['id_order'];
            $missionsByOrderId[$orderId] = [
                "id_order" => $record['id_order'],
                "date_add" => $record['date_add'],
                "missions" =>  array_merge(
                    isset($missionsByOrderId[$orderId]) ?
                        $missionsByOrderId[$orderId]["missions"] :
                        [],
                    [array_merge($record, [
                        "offer" => $offers[$record["id_offer"]],
                        "product" => new Product($record["id_product"], false, Context::getContext()->language->id),
                        "canResubmit" => $record["dealt_status_mission"] == "DRAFT" || $record["dealt_status_mission"] == "CANCELED",
                        "canCancel" => $record["dealt_status_mission"] == "DRAFT" || $record["dealt_status_mission"] == "SUBMITTED"
                    ])]
                )
            ];
        }

        $records = new RecordCollection($missionsByOrderId);

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
