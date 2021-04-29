<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Serializer;

use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Normalizer implements NormalizerInterface, DenormalizerInterface
{
    private ObjectNormalizer $normalizer;
    private EntityManagerInterface $entityManager;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        ObjectNormalizer $normalizer,
        EntityManagerInterface $entityManager,
        PropertyAccessorInterface $propertyAccessor
    )
    {
        $this->normalizer = $normalizer;
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    private function classMetadata(string $className): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($className);
    }

    private function assocTargetClass(string $className, string $associationFieldName): string
    {
        return $this->classMetadata($className)->getAssociationTargetClass($associationFieldName);
    }

    private function entityIdFieldName(string $className): string
    {
        return current($this->classMetadata($className)->getIdentifier());
    }

    private function normalizedAssociationFields(object $object): array
    {
        $className = get_class($object);
        $classMetadata = $this->classMetadata($className);

        $output = [];

        foreach ($classMetadata->getAssociationNames() as $fieldName) {
            $associatedClassName = $this->assocTargetClass($className, $fieldName);
            $associatedClassIdFieldName = $this->entityIdFieldName($associatedClassName);

            $normalizedValue = null;

            if ($classMetadata->isSingleValuedAssociation($fieldName)) {
                $normalizedValue = $this->propertyAccessor->getValue($object, $fieldName . '.' . $associatedClassIdFieldName);
            } elseif ($classMetadata->isCollectionValuedAssociation($fieldName)) {
                $normalizedValue = [];
                foreach ($this->propertyAccessor->getValue($object, $fieldName) as $associatedEntity) {
                    $normalizedValue[] = $this->propertyAccessor->getValue($associatedEntity, $associatedClassIdFieldName);
                }
            } else {
                continue;
            }

            $output[$fieldName] = $normalizedValue;
        }

        return $output;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedAssociations = $this->normalizedAssociationFields($object);
        $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = array_keys($normalizedAssociations);
        $data = $this->normalizer->normalize($object, $format, $context);
        return array_merge($data, $normalizedAssociations);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof RapidApiCrudEntity;
    }

    private function denormalizeAssociations(RapidApiCrudEntity $entity, array $data)
    {
        $className = get_class($entity);
        $classMetadata = $this->classMetadata(get_class($entity));

        foreach ($classMetadata->getAssociationNames() as $fieldName) {
            if (!isset($data[$fieldName])) {
                continue;
            }

            $associatedClassName = $this->assocTargetClass($className, $fieldName);

            if ($classMetadata->isSingleValuedAssociation($fieldName)) {
                $fieldValue = $this->entityManager->find($associatedClassName, $data[$fieldName]);
                $this->propertyAccessor->setValue($entity, $fieldName, $fieldValue);
            } elseif ($classMetadata->isCollectionValuedAssociation($fieldName)) {
                $fieldValue = new ArrayCollection();
                foreach ($data[$fieldName] as $idToAssociate) {
                    $fieldValue->add($this->entityManager->find($associatedClassName, $idToAssociate));
                }
                $this->propertyAccessor->setValue($entity, $fieldName, $fieldValue);
            }

        }
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $classMetadata = $this->classMetadata($type);

        $associationData = [];
        foreach ($classMetadata->getAssociationNames() as $associationName) {
            if (isset($data[$associationName])) {
                $associationData[$associationName] = $data[$associationName];
                unset($data[$associationName]);
            }
        }

        $entity = $this->normalizer->denormalize($data, $type, $format, $context);
        $this->denormalizeAssociations($entity, $associationData);

        return $entity;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        //TODO: Check if $type is implementing App\Entity\CrudEntity

        return true;
    }
}