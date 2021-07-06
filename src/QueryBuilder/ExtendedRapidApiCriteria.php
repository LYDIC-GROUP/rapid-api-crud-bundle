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

        //Walk over all nodes
        $expression = $this->walk($queryBuilder, $nodes);

        $this->joinableEntities = array_unique($this->joinableEntities);
        foreach ($this->joinableEntities as $joinableEntity) {
            $queryBuilder->join('entity.' . $joinableEntity, $joinableEntity);
        }

        $queryBuilder->andWhere($expression);

        return $queryBuilder;
    }

    /**
     * Walk over every Node in the tree
     * if we find a filter or operator store them
     * otherwise recursivly walk over the filter group which contains filtes and operators
     *
     * @param $queryBuilder
     * @param $nodes
     * @return \Doctrine\ORM\Query\Expr\Andx|\Doctrine\ORM\Query\Expr\Orx|mixed
     */
    public function walk($queryBuilder, $nodes)
    {
        $expressions = [];
        $operators = [];
        foreach ($nodes as $node) {
            if ($node instanceof FilterGroup) {
                $expressions[] = $this->walk($queryBuilder, $node->getNodes());
            } elseif ($node instanceof Filter) {
                $expressions[] = $this->getFilterExpression($queryBuilder, $node);
            } elseif ($node instanceof FilterLogicOperator) {
                $operators[] = $node->getOperator();
            }
        }

        return $this->buildExpression($queryBuilder, $expressions, $operators);
    }

    protected function getFilterExpression(QueryBuilder $queryBuilder, Filter $node)
    {
        $prefix = $this->propertyPrefix;
        if (strpos($node->getProperty(), '.')) {
            $prefix = '';
            $this->joinableEntities[] = explode('.', $node->getProperty())[0];
        }
        $parameterName = ':' . str_replace('.', '_', $node->getProperty()) . '_' . $this->parameterCount;
        $expression = null;
        switch ($node->getOperator()) {
            case'eq':
                $expression = $queryBuilder->expr()->eq($prefix . $node->getProperty(), $parameterName);
                $queryBuilder->setParameter($parameterName, $node->getValue());
                break;
            case'neq':
                $expression = $queryBuilder->expr()->neq($prefix . $node->getProperty(), $parameterName);
                $queryBuilder->setParameter($parameterName, $node->getValue());
                break;
            case'gt':
                $expression = $queryBuilder->expr()->gt($prefix . $node->getProperty(), $parameterName);
                $queryBuilder->setParameter($parameterName, $node->getValue());
                break;
            case 'lt':
                $expression = $queryBuilder->expr()->lt($prefix . $node->getProperty(), $parameterName);
                $queryBuilder->setParameter($parameterName, $node->getValue());
                break;
            case 'gte':
                $expression = $queryBuilder->expr()->gte($prefix . $node->getProperty(), $parameterName);
                $queryBuilder->setParameter($parameterName, $node->getValue());
                break;
            case'lte':
                $expression = $queryBuilder->expr()->lte($prefix . $node->getProperty(), $parameterName);
                $queryBuilder->setParameter($parameterName, $node->getValue());
                break;
            case 'like':
                $expression = $queryBuilder->expr()->like($prefix . $node->getProperty(), $parameterName);
                $queryBuilder->setParameter($parameterName, '%' . $node->getValue() . '%');
                break;
        }

        return $expression;
    }

    protected function buildExpression(QueryBuilder $queryBuilder, array $expressions, array $operators)
    {
        $expression = array_shift($expressions);
        foreach ($operators as $key => $operator) {
            switch ($operator) {
                case "AND":
                    $expression = $queryBuilder->expr()->andX($expression)->add($expressions[$key]);
                    break;
                case "OR":
                    $expression = $queryBuilder->expr()->orX($expression)->add($expressions[$key]);
                    break;
            }
        }

        return $expression;
    }
}
