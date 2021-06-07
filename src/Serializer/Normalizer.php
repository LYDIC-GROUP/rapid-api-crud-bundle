<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Serializer;

use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Normalizer extends Serializer implements NormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(
        ObjectNormalizer $normalizer,
        EntityManagerInterface $entityManager
    )
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        parent::__construct($entityManager, $propertyAccessor);
        $this->normalizer = $normalizer;
    }

    private function entityIdFieldName(string $className): string
    {
        return current($this->classMetadata($className)->getIdentifier());
    }

    /**
     * @throws ExceptionInterface
     */
    private function normalizedAssociationFields(object $object, array $context): array
    {
        $assocFieldsToNormalizeToEntity = $this->assocFieldsToNormalizeToEntity($context);

        $className = get_class($object);
        $classMetadata = $this->classMetadata($className);

        $output = [];
        foreach ($classMetadata->getAssociationNames() as $fieldName) {
            $associatedClassName = $this->assocTargetClass($className, $fieldName);
            $associatedClassIdFieldName = $this->entityIdFieldName($associatedClassName);

            $normalizeToEntity = in_array($fieldName, $assocFieldsToNormalizeToEntity);

            try {
                $normalizedValue = null;
                if ($classMetadata->isSingleValuedAssociation($fieldName)) {
                    if ($normalizeToEntity) {
                        $associatedEntity = $this->propertyAccessor->getValue($object, $fieldName);
                        $normalizedValue = $this->normalize($associatedEntity, null, ['groups' => $context['groups']]);
                    } else {
                        $normalizedValue = $this->propertyAccessor->getValue($object, $fieldName . '.' . $associatedClassIdFieldName);
                    }
                } elseif ($classMetadata->isCollectionValuedAssociation($fieldName)) {
                    $normalizedValue = [];
                    foreach ($this->propertyAccessor->getValue($object, $fieldName) as $associatedEntity) {
                        if ($normalizeToEntity) {
                            $normalizedValue[] = $this->normalize($associatedEntity, null, ['groups' => $context['groups']]);
                        } else {
                            $normalizedValue[] = $this->propertyAccessor->getValue($associatedEntity, $associatedClassIdFieldName);
                        }
                    }
                } else {
                    $output[$fieldName] = null;
                }
            } catch (UnexpectedTypeException $ex) {
                //The value is probably null
                $output[$fieldName] = null;
            }

            $output[$fieldName] = $normalizedValue;
        }

        return $output;
    }


    private function assocFieldsToNormalizeToEntity(array $context): array
    {
        if (empty($context['include'])) {
            return [];
        }

        return explode(',', $context['include']);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedAssociations = $this->normalizedAssociationFields($object, $context);
        $context[AbstractNormalizer::IGNORED_ATTRIBUTES] = array_keys($normalizedAssociations);
        $data = $this->normalizer->normalize($object, $format, $context);
        return array_merge($data, $normalizedAssociations);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof RapidApiCrudEntity;
    }
}
