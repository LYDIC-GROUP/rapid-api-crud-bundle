<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 11:16 PM
 */

namespace LydicGroup\RapidApiCrudBundle\QueryBuilder;

use Doctrine\ORM\QueryBuilder;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Enum\SorterMode;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\BasicRapidApiCriteria;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiSortInterface;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use Symfony\Component\HttpFoundation\Request;

class BasicRapidApiSort implements RapidApiSortInterface
{
    private RapidApiContextProvider $contextProvider;

    private string $expressionKey;
    private string $propertyPrefix;

    /**
     * SorterFactory constructor.
     */
    public function __construct(RapidApiContextProvider $contextProvider)
    {
        $this->contextProvider = $contextProvider;

        $this->expressionKey = 'sort';
        $this->propertyPrefix = 'entity.';
    }

    public function getSorterMode(): int
    {
        return SorterMode::BASIC;
    }

    public function get(QueryBuilder $queryBuilder): QueryBuilder
    {
        $context = $this->contextProvider->getContext();
        $sortString = $context->getRequest()->get($this->expressionKey);

        if (empty($sortString)) {
            return $queryBuilder;
        }

        $sorts = explode(',', $sortString);
        foreach ($sorts as $sort) {
            list($property, $direction) = explode(' ', $sort);

            $queryBuilder->addOrderBy($this->propertyPrefix . $property, $direction);
        }

        return $queryBuilder;
    }
}