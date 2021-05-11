<?php
/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 9:39 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Factory;

use LydicGroup\Filtering\ExpressionParser;
use LydicGroup\RapidApiCrudBundle\Builder\BasicQueryBuilder;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Enum\FilterMode;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\BasicRapidApiCriteria;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\BasicRapidApiSort;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\ExtendedRapidApiCriteria;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiCriteriaInterface;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiSortInterface;
use Symfony\Component\HttpFoundation\Request;

class SortFactory
{
    public function create(ControllerConfig $config, Request $request): RapidApiSortInterface
    {
        $builder = new BasicRapidApiSort();
        $builder->setRequest($request);
        $builder->setConfig($config);
        return $builder;
    }
}
