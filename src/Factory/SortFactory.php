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
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use Symfony\Component\HttpFoundation\Request;

class SortFactory
{
    private array $sorters;

    /**
     * @param array $criteria
     * @return CriteriaFactory
     */
    public function addSorter(RapidApiSortInterface $sorter): SortFactory
    {
        $this->sorters[$sorter->getSorterMode()] = $sorter;

        return $this;
    }

    public function create(RapidApiContext $context): RapidApiSortInterface
    {
        return $this->sorters[$context->getConfig()->getSorterMode()];
    }
}
