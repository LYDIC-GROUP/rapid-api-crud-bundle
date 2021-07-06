<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 10:07 PM
 */

namespace LydicGroup\RapidApiCrudBundle\QueryBuilder;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Mapping\ClassMetadata;
use LydicGroup\Filtering\ExpressionParser;
use LydicGroup\Filtering\Filter;
use LydicGroup\Filtering\FilterGroup;
use LydicGroup\Filtering\FilterLogicOperator;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Enum\FilterMode;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use Symfony\Component\HttpFoundation\Request;

class ExtendedRapidApiCriteria implements RapidApiCriteriaInterface
{
    private RapidApiContextProvider $contextProvider;

    private ExpressionParser $parser;
    private string $expressionKey;
    private string $propertyPrefix;
    private int $parameterCount;
    private array $joinableEntities;

    /**
     * FilterFactory constructor.
     * @param ExpressionParser $parser
     */
    public function __construct(RapidApiContextProvider $contextProvider, ExpressionParser $parser)
    {
        $this->contextProvider = $contextProvider;

        $this->parser = $parser;
        $this->expressionKey = 'filter';
        $this->propertyPrefix = 'entity.';
        $this->joinableEntities = [];
    }

    public function getFilterMode(): int
    {
        return FilterMode::EXTENDED;
    }

    public function get(QueryBuilder $queryBuilder): QueryBuilder
    {
        $context = $this->contextProvider->getContext();

        $this->parameterCount = 0;
        $this->joinableEntities = [];

        $filter = urldecode($context->getRequest()->get($this->expressionKey));

        //If no filters Applied
        if ($filter == null) {
            return $queryBuilder;
        }

        $fst = $this->parser->parse($filter);
        $nodes = $fst->getNodes();

        $expression = $this->getFilterNodeExpression($nodes[0], $queryBuilder);
        $expression = $this->walker($queryBuilder, $nodes, $expression);

        $this->joinableEntities = array_unique($this->joinableEntities);
        foreach ($this->joinableEntities as $joinableEntity) {
            $queryBuilder->join('entity.' . $joinableEntity, $joinableEntity);
        }

        $queryBuilder->andWhere($expression);

        return $queryBuilder;
    }

    /**
     * @param array $nodes
     * @param Expression $expression
     */
    private function walker(QueryBuilder $queryBuilder, array $nodes, $expression = null)
    {
        $currentPos = 1;
        while ($currentPos <= count($nodes)) {
            $expression = $this->buildQuery($queryBuilder, $expression, $nodes, $currentPos);
            $currentPos += 2;
        }

        return $expression;
    }

    /**
     * @param ExpressionBuilder $queryBuilder
     * @param $nodes
     * @return mixed
     */
    private function buildQuery(QueryBuilder $queryBuilder, $expression, $nodes, $currentPos = 1)
    {
        /** @var FilterLogicOperator $logicNode */
        $logicNode = $nodes[$currentPos] ?? null;
        $nextNode = $nodes[$currentPos + 1] ?? null;

        //the end?
        if ($logicNode == null) {
            //return the current expression
            return $expression;
        }

        $expression1 = $this->getFilterNodeExpression($nextNode, $queryBuilder, $expression);

        return $this->getLogicNodeExpression($queryBuilder, $logicNode, $expression, $expression1);
    }


    /**
     * @param $node
     * @param ExpressionBuilder $queryBuilder
     * @param Expression $expression
     * @return Criteria|\Doctrine\Common\Collections\Expr\Comparison|Expression|ExpressionBuilder
     */
    private function getFilterNodeExpression($node, QueryBuilder $queryBuilder, $expression = null)
    {


        $expression1 = null;
        if ($node instanceof FilterGroup) {
            $expression1 = $this->walker($queryBuilder, $node->getNodes(), $expression);
        } elseif ($node instanceof Filter) {
            $prefix = $this->propertyPrefix;
            if (strpos($node->getProperty(), '.')) {
                $prefix = '';
                $this->joinableEntities[] = explode('.', $node->getProperty())[0];
            }
            $parameterName = ':' . str_replace('.', '_', $node->getProperty()) . '_' . $this->parameterCount;
            switch ($node->getOperator()) {
                case'eq':
                    $expression1 = $queryBuilder->expr()->eq($prefix . $node->getProperty(), $parameterName);
                    $queryBuilder->setParameter($parameterName, $node->getValue());
                    break;
                case'neq':
                    $expression1 = $queryBuilder->expr()->neq($prefix . $node->getProperty(), $parameterName);
                    $queryBuilder->setParameter($parameterName, $node->getValue());
                    break;
                case'gt':
                    $expression1 = $queryBuilder->expr()->gt($prefix . $node->getProperty(), $parameterName);
                    $queryBuilder->setParameter($parameterName, $node->getValue());
                    break;
                case 'lt':
                    $expression1 = $queryBuilder->expr()->lt($prefix . $node->getProperty(), $parameterName);
                    $queryBuilder->setParameter($parameterName, $node->getValue());
                    break;
                case 'gte':
                    $expression1 = $queryBuilder->expr()->gte($prefix . $node->getProperty(), $parameterName);
                    $queryBuilder->setParameter($parameterName, $node->getValue());
                    break;
                case'lte':
                    $expression1 = $queryBuilder->expr()->lte($prefix . $node->getProperty(), $parameterName);
                    $queryBuilder->setParameter($parameterName, $node->getValue());
                    break;
                case 'like':
                    $expression1 = $queryBuilder->expr()->like($prefix . $node->getProperty(), $parameterName);
                    $queryBuilder->setParameter($parameterName, '%' . $node->getValue() . '%');
                    break;
            }
        }
        $this->parameterCount++;

        return $expression1;
    }


    private function getLogicNodeExpression(QueryBuilder $queryBuilder, FilterLogicOperator $logicNode, $expressionA, $expressionB)
    {
        switch ($logicNode->getOperator()) {
            case "AND":
                return $queryBuilder->expr()->andX($expressionA, $expressionB);
            case "OR":
                return $queryBuilder->expr()->orX($expressionA, $expressionB);
        }

        throw new \LogicException('Impossible');
    }
}
