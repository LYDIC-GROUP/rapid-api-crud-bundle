<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Serializer;

use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Normalizer extends Serializer implements NormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(
        ObjectNormalizer $normalizer,
        EntityManagerInterface $entityManager,
        PropertyAccessorInterface $propertyAccessor
    )
    {
        parent::__construct($entityManager, $propertyAccessor);
        $this->normalizer = $normalizer;
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
}