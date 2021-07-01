<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Enum\SerializerGroups;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Factory\EntityRepositoryFactory;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class UpdateEntityCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CrudService $crudService;
    private EntityRepositoryFactory $entityRepositoryFactory;

    public function __construct(EntityManagerInterface $entityManager, CrudService $crudService, EntityRepositoryFactory $entityRepositoryFactory)
    {
        $this->entityManager = $entityManager;
        $this->crudService = $crudService;
        $this->entityRepositoryFactory = $entityRepositoryFactory;
    }

    /**
     * @throws ExceptionInterface
     * @throws RapidApiCrudException
     */
    public function __invoke(UpdateEntityCommand $command): RapidApiCrudEntity
    {
        $entityRepository = $this->entityRepositoryFactory->createEntityRepository();
        $data = $command->data;

        $entity = $entityRepository->find($command->className, $command->id);
        $entity = $this->crudService->arrayToEntity($data, $command->className, SerializerGroups::UPDATE, $entity);

        $this->crudService->validate($entity);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }
}
