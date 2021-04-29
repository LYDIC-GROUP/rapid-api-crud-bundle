<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\HttpFoundation\Request;

class CriteriaBuilder
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function classMetadata(string $className): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($className);
    }

    public function build(string $className, Request $request): array
    {
        $criteria = [];

        foreach ($this->fieldAndAssocNames($className) as $name) {
            if ($request->query->has($name)) {
                $criteria[$name] = $request->query->get($name);
            }
        }

        return $criteria;
    }

    private function fieldAndAssocNames(string $className): array
    {
        return array_merge(
            $this->classMetadata($className)->getFieldNames(),
            $this->classMetadata($className)->getAssociationNames(),
        );
    }
}