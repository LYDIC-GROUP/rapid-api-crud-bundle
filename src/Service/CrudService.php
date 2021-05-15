<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use LydicGroup\RapidApiCrudBundle\Builder\CriteriaBuilder;
use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use LydicGroup\RapidApiCrudBundle\Exception\ValidationException;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiCriteriaInterface;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiSortInterface;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepository;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
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
    private EntityRepositoryInterface $entityRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CriteriaBuilder $crudCriteriaBuilder,
        ValidatorInterface $validator,
        NormalizerInterface $objectNormalizer,
        DenormalizerInterface $objectDenormalizer,
        MessageBusInterface $messageBus,
        EntityRepositoryInterface $entityRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->crudCriteriaBuilder = $crudCriteriaBuilder;
        $this->validator = $validator;
        $this->objectNormalizer = $objectNormalizer;
        $this->objectDenormalizer = $objectDenormalizer;
        $this->messageBus = $messageBus;
        $this->entityRepository = $entityRepository;
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
    public function list(string $className, int $page , int $limit, RapidApiCriteriaInterface $criteria, RapidApiSortInterface $sorter): Paginator
    {
        $queryBuilder = $this->entityRepository->getQueryBuilder($className);

        //Apply filtering
        $queryBuilder = $criteria->get($queryBuilder);
        //Apply sorts
        $queryBuilder = $sorter->get($queryBuilder);

        //Set paging limits
        $queryBuilder->setFirstResult(($page - 1) * $limit);
        $queryBuilder->setMaxResults($limit);

        return new Paginator($queryBuilder);
    }

    /**
     * @throws ExceptionInterface
     * @throws NotFoundException
     */
    public function find(string $entityClassName, string $id): RapidApiCrudEntity
    {
        $entity = $this->entityRepository->find($entityClassName, $id);
        if (!$entity instanceof RapidApiCrudEntity) {
            throw new NotFoundException();
        }

        return $entity;
    }

    public function create(string $entityClassName, array $data): RapidApiCrudEntity
    {
        $command = new CreateEntityCommand($entityClassName, $data);
        $envelope = $this->messageBus->dispatch($command);

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);
        return $stamp->getResult();
    }

    public function update(string $entityClassName, string $id, array $data): RapidApiCrudEntity
    {
        $command = new UpdateEntityCommand($entityClassName, $id, $data);
        $envelope = $this->messageBus->dispatch($command);

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);
        return $stamp->getResult();
    }

    public function delete(string $entityClassName, string $id): void
    {
        $command = new DeleteEntityCommand($entityClassName, $id);
        $this->messageBus->dispatch($command);
    }
}