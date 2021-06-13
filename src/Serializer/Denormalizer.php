<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Serializer;

use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Denormalizer extends Serializer implements DenormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(
        EntityManagerInterface $entityManager,
        PropertyAccessorInterface $propertyAccessor,
        ObjectNormalizer $normalizer
    )
    {
        parent::__construct($entityManager, $propertyAccessor);
        $this->normalizer = $normalizer;
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
        return in_array(RapidApiCrudEntity::class, class_implements($type));
    }
}