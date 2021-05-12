<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Serializer
{
    protected EntityManagerInterface $entityManager;
    protected PropertyAccessorInterface $propertyAccessor;

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor)
    {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    protected function classMetadata(string $className): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($className);
    }

    protected function assocTargetClass(string $className, string $associationFieldName): string
    {
        return $this->classMetadata($className)->getAssociationTargetClass($associationFieldName);
    }
}