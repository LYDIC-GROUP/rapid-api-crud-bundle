<?php

namespace LydicGroup\RapidApiCrudBundle\QueryBuilder;

use Doctrine\ORM\QueryBuilder;
use LydicGroup\RapidApiCrudBundle\Enum\FilterMode;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;

class DqlRapidApiCriteria implements RapidApiCriteriaInterface
{
    private RapidApiContextProvider $contextProvider;

    /**
     * BasicRapidApiCriteria constructor.
     * @param RapidApiContextProvider $contextProvider
     */
    public function __construct(RapidApiContextProvider $contextProvider)
    {
        $this->contextProvider = $contextProvider;
    }

    public function getFilterMode(): int
    {
        return FilterMode::DQL;
    }

    public function get(QueryBuilder $queryBuilder): QueryBuilder
    {
        $dql = $this->contextProvider->getContext()->getRequest()->query->get('filter');

        if (!empty($dql)) {
            $queryBuilder->where($dql);
        }

        return $queryBuilder;
    }
}
