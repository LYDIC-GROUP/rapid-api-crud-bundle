<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use LydicGroup\RapidApiCrudBundle\Builder\CriteriaBuilder;
use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use LydicGroup\RapidApiCrudBundle\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CrudService
{
    private EntityManagerInterface $entityManager;
    private CriteriaBuilder $crudCriteriaBuilder;
    private ValidatorInterface $validator;
    private NormalizerInterface $objectNormalizer;
    private DenormalizerInterface $objectDenormalizer;
    private MessageBusInterface $messageBus;

    public function __construct(
        EntityManagerInterface $entityManager,
        CriteriaBuilder $crudCriteriaBuilder,
        ValidatorInterface $validator,
        NormalizerInterface $objectNormalizer,
        DenormalizerInterface $objectDenormalizer,
        MessageBusInterface $messageBus
    )
    {
        $this->entityManager = $entityManager;
        $this->crudCriteriaBuilder = $crudCriteriaBuilder;
        $this->validator = $validator;
        $this->objectNormalizer = $objectNormalizer;
        $this->objectDenormalizer = $objectDenormalizer;
        $this->messageBus = $messageBus;
    }

    public function entityRepository(string $className): ObjectRepository
    {
        return $this->entityManager->getRepository($className);
    }

    /**
     * @throws NotFoundException
     */
    public function entityById(string $className, string $id): object
    {
        $classMetadata = $this->entityManager->getClassMetadata($className);
        $entityIdFieldName = current($classMetadata->getIdentifier());
        $entity = $this->entityRepository($className)->findOneBy([$entityIdFieldName => $id]);
        if (is_null($entity)) {
            throw new NotFoundException();
        }

        return $entity;
    }

    /**
     * @throws ValidationException
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

        throw new ValidationException(implode(', ', $errorMessages));
    }

    /**
     * @throws ExceptionInterface
     */
    public function entityToArray(RapidApiCrudEntity $entity, string $group = 'find'): array
    {
        return $this->objectNormalizer->normalize($entity, null, ['groups' => [$group]]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function arrayToEntity(array $data, string $className, string $group, object $objectToPopulate = null): RapidApiCrudEntity
    {
        $context = ['groups' => [$group]];

        if ($objectToPopulate) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $objectToPopulate;
        }
        return $this->objectDenormalizer->denormalize($data, $className, null, $context);
    }

    public function criteria(string $entityClassName, Request $request): array
    {
        return $this->crudCriteriaBuilder->build($entityClassName, $request);
    }

    /**
     * @throws ExceptionInterface
     */
    public function list(string $entityClassName, array $criteria): array
    {
        $className = $entityClassName;
        $output = [];

        foreach ($this->entityRepository($className)->findBy($criteria) as $entity) {
            if (!$entity instanceof RapidApiCrudEntity) {
                continue;
            }

            $output[] = $this->entityToArray($entity, 'list');
        }

        return $output;
    }

    /**
     * @throws ExceptionInterface
     * @throws NotFoundException
     */
    public function find(string $entityClassName, string $id): array
    {
        $entity = $this->entityById($entityClassName, $id);
        if (!$entity instanceof RapidApiCrudEntity) {
            return [];
        }

        return $this->entityToArray($entity);
    }

    public function create(string $entityClassName, array $data): void
    {
        $command = new CreateEntityCommand($entityClassName, $data);
        $this->messageBus->dispatch($command);
    }

    public function update(string $entityClassName, string $id, array $data): void
    {
        $command = new UpdateEntityCommand($entityClassName, $id, $data);
        $this->messageBus->dispatch($command);
    }

    public function delete(string $entityClassName, string $id): void
    {
        $command = new DeleteEntityCommand($entityClassName, $id);
        $this->messageBus->dispatch($command);
    }
}