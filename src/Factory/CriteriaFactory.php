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
use LydicGroup\RapidApiCrudBundle\QueryBuilder\ExtendedRapidApiCriteria;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiCriteriaInterface;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use Symfony\Component\HttpFoundation\Request;

class CriteriaFactory
{
    private array $criteria;

    /**
     * @param array $criteria
     * @return CriteriaFactory
     */
    public function addCriteria(RapidApiCriteriaInterface $criteria): CriteriaFactory
    {
        $this->criteria[$criteria->getFilterMode()] = $criteria;

        return $this;
    }

    /**
     * @param RapidApiContext $context
     * @return RapidApiCriteriaInterface
     */
    public function create(RapidApiContext $context): RapidApiCriteriaInterface
    {
        return $this->criteria[$context->getConfig()->getFilterMode()];
    }
}
