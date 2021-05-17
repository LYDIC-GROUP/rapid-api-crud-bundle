<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 10:07 PM
 */

namespace LydicGroup\RapidApiCrudBundle\QueryBuilder;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Mapping\ClassMetadata;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Enum\FilterMode;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use Symfony\Component\HttpFoundation\Request;

class BasicRapidApiCriteria implements RapidApiCriteriaInterface
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
        return FilterMode::BASIC;
    }

    public function get(QueryBuilder $queryBuilder): QueryBuilder
    {
        $context = $this->contextProvider->getContext();
        foreach ($this->fieldAndAssocNames($queryBuilder) as $name) {
            if ($context->getRequest()->query->has($name)) {
                $queryBuilder->andWhere(sprintf('entity.%s = :%s', $name, $name));
                $queryBuilder->setParameter($name, $context->getRequest()->query->get($name));
            }
        }

        return $queryBuilder;
    }

    private function classMetadata(QueryBuilder $queryBuilder): ClassMetadata
    {
        $context = $this->contextProvider->getContext();
        return $queryBuilder->getEntityManager()->getClassMetadata($context->getEntityClassName());
    }

    private function fieldAndAssocNames(QueryBuilder $queryBuilder): array
    {
        return array_merge(
            $this->classMetadata($queryBuilder)->getFieldNames(),
            $this->classMetadata($queryBuilder)->getAssociationNames(),
        );
    }
}
