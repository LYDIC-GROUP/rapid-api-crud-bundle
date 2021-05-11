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
use LydicGroup\RapidApiCrudBundle\QueryBuilder\BasicRapidApiCriteria;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiSortInterface;
use Symfony\Component\HttpFoundation\Request;

class BasicRapidApiSort implements RapidApiSortInterface
{
    private Request $request;
    private ControllerConfig $config;

    private string $expressionKey;
    private string $propertyPrefix;

    /**
     * SorterFactory constructor.
     */
    public function __construct()
    {
        $this->expressionKey = 'sort';
        $this->propertyPrefix = 'entity.';
    }

    /**
     * @param Request $request
     * @return BasicRapidApiCriteria
     */
    public function setRequest(Request $request): RapidApiSortInterface
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param ControllerConfig $config
     * @return BasicRapidApiCriteria
     */
    public function setConfig(ControllerConfig $config): RapidApiSortInterface
    {
        $this->config = $config;
        return $this;
    }

    public function get(QueryBuilder $queryBuilder): QueryBuilder
    {
        $sortString = $this->request->get($this->expressionKey);

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