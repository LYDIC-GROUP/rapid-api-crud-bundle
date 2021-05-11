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
use Symfony\Component\HttpFoundation\Request;

class BasicRapidApiCriteria implements RapidApiCriteriaInterface
{
    private Request $request;
    private ControllerConfig $config;

    /**
     * @param Request $request
     * @return BasicRapidApiCriteria
     */
    public function setRequest(Request $request): RapidApiCriteriaInterface
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param ControllerConfig $config
     * @return BasicRapidApiCriteria
     */
    public function setConfig(ControllerConfig $config): RapidApiCriteriaInterface
    {
        $this->config = $config;
        return $this;
    }

    public function get(QueryBuilder $queryBuilder): QueryBuilder
    {
        foreach ($this->fieldAndAssocNames($queryBuilder) as $name) {
            if ($this->request->query->has($name)) {
                $queryBuilder->andWhere(sprintf('entity.%s = :%s', $name, $name));
                $queryBuilder->setParameter($name, $this->request->query->get($name));
            }
        }

        return $queryBuilder;
    }

    private function classMetadata(QueryBuilder $queryBuilder): ClassMetadata
    {
        return $queryBuilder->getEntityManager()->getClassMetadata($this->config->getEntityClassName());
    }

    private function fieldAndAssocNames(QueryBuilder $queryBuilder): array
    {
        return array_merge(
            $this->classMetadata($queryBuilder)->getFieldNames(),
            $this->classMetadata($queryBuilder)->getAssociationNames(),
        );
    }
}
