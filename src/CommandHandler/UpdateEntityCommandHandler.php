<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class UpdateEntityCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CrudService $crudService;
    private EntityRepositoryInterface $entityRepository;

    public function __construct(EntityManagerInterface $entityManager, CrudService $crudService, EntityRepositoryInterface $entityRepository)
    {
        $this->entityManager = $entityManager;
        $this->crudService = $crudService;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @throws ExceptionInterface
     * @throws RapidApiCrudException
     */
    public function __invoke(UpdateEntityCommand $command): RapidApiCrudEntity
    {
        $data = $command->data;

        $entity = $this->entityRepository->find($command->className, $command->id);
        $entity = $this->crudService->arrayToEntity($data, $command->className, 'update', $entity);

        $this->crudService->validate($entity);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }
}