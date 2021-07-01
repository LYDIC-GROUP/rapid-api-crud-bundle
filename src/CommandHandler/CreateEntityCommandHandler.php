<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Enum\SerializerGroups;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Factory\EntityRepositoryFactory;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class CreateEntityCommandHandler implements MessageHandlerInterface
{
    private EntityRepositoryFactory $entityRepositoryFactory;
    private CrudService $crudService;

    public function __construct(EntityRepositoryFactory $entityRepositoryFactory, CrudService $crudService)
    {
        $this->entityRepositoryFactory = $entityRepositoryFactory;
        $this->crudService = $crudService;
    }

    /**
     * @throws ExceptionInterface
     * @throws RapidApiCrudException
     */
    public function __invoke(CreateEntityCommand $command): RapidApiCrudEntity
    {
        $data = $command->data;

        $entity = $this->crudService->arrayToEntity($data, $command->className, SerializerGroups::CREATE);

        $this->crudService->validate($entity);

        $entityRepository = $this->entityRepositoryFactory->createEntityRepository();
        $entityRepository->persist($entity);

        return $entity;
    }
}
