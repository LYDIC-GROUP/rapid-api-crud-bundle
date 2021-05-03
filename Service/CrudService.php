<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use LydicGroup\RapidApiCrudBundle\Builder\CriteriaBuilder;
use LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\Inflector\InflectorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CrudService
{
    private EntityManagerInterface $entityManager;
    private CriteriaBuilder $crudCriteriaBuilder;
    private ValidatorInterface $validator;
    private InflectorInterface $inflector;
    private NormalizerInterface $objectNormalizer;
    private DenormalizerInterface $objectDenormalizer;

    public function __construct(
        EntityManagerInterface $entityManager,
        CriteriaBuilder $crudCriteriaBuilder,
        ValidatorInterface $validator,
        NormalizerInterface $objectNormalizer,
        DenormalizerInterface $objectDenormalizer
    )
    {
        $this->entityManager = $entityManager;
        $this->crudCriteriaBuilder = $crudCriteriaBuilder;
        $this->validator = $validator;
        $this->inflector = new EnglishInflector();
        $this->objectNormalizer = $objectNormalizer;
        $this->objectDenormalizer = $objectDenormalizer;
    }

    private function entityRepository(string $className): ObjectRepository
    {
        return $this->entityManager->getRepository($className);
    }

    private function entityIdFieldName(string $className): string
    {
        $classMetadata = $this->classMetadata($className);
        return current($classMetadata->getIdentifier());
    }

    /**
     * @throws RapidApiCrudException
     */
    public function entityById(string $className, string $id): object
    {
        $entityIdFieldName = $this->entityIdFieldName($className);
        $entity = $this->entityRepository($className)->findOneBy([$entityIdFieldName => $id]);
        if (is_null($entity)) {
            throw new RapidApiCrudException($this->notFoundMessage($className));
        }

        return $entity;
    }

    /**
     * @throws \ReflectionException
     */
    private function classNameWithoutNamespace(string $className): string
    {
        $reflect = new \ReflectionClass($className);
        return $reflect->getShortName();
    }

    /**
     * @throws \ReflectionException
     */
    public function entityNameSingular(string $className): string
    {
        return strtolower(current($this->inflector->singularize($this->classNameWithoutNamespace($className))));
    }

    private function classMetadata(string $className): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($className);
    }

    /**
     * @throws RapidApiCrudException
     */
    public function validate(RapidApiCrudEntity $entity)
    {
        $errors = $this->validator->validate($entity);
        if ($errors->count() < 1) {
            return;
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        throw new RapidApiCrudException(implode(', ', $errorMessages));
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(RapidApiCrudEntity $entity, string $group): array
    {
        return $this->objectNormalizer->normalize($entity, null, ['groups' => [$group]]);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize(array $data, string $className, string $group, object $objectToPopulate = null): RapidApiCrudEntity
    {
        $context = [
            'groups' => [$group]
        ];

        if ($objectToPopulate) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $objectToPopulate;
        }
        return $this->objectDenormalizer->denormalize($data, $className, null, $context);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function list(ControllerConfig $controllerConfig, Request $request): array
    {
        $className = $controllerConfig->entityClassName;
        $criteria = $this->crudCriteriaBuilder->build($className, $request);
        $output = [];

        foreach ($this->entityRepository($className)->findBy($criteria) as $entity) {
            $output[] = $this->normalize($entity, 'list');
        }

        return $output;
    }

    /**
     * @throws RapidApiCrudException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function find(ControllerConfig $controllerConfig, $id): array
    {
        $entity = $this->entityById($controllerConfig->entityClassName, $id);
        return $this->normalize($entity, 'find');
    }

    public function notFoundMessage(string $className): string
    {
        return sprintf("The %s doesn't exist.", $this->entityNameSingular($className));
    }

    /**
     * @throws \ReflectionException
     */
    public function createdMessage(string $className): string
    {
        return sprintf("The %s has been created successfully.", $this->entityNameSingular($className));
    }

    /**
     * @throws \ReflectionException
     */
    public function updatedMessage(string $className): string
    {
        return sprintf("The %s has been updated successfully.", $this->entityNameSingular($className));
    }

    /**
     * @throws \ReflectionException
     */
    public function deletedMessage(string $className): string
    {
        return sprintf("The %s has been deleted successfully.",$this->entityNameSingular($className));
    }
}