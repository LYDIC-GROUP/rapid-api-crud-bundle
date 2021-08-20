<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use LydicGroup\RapidApiCrudBundle\Event\AfterEntityUpdatedEvent;
use LydicGroup\RapidApiCrudBundle\Event\BeforeEntityUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Enum\SerializerGroups;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Factory\EntityRepositoryFactory;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UpdateEntityCommandHandler implements MessageHandlerInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;
    private CrudService $crudService;
    private EntityRepositoryFactory $entityRepositoryFactory;
    private RapidApiContextProvider $contextProvider;

    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager, CrudService $crudService, EntityRepositoryFactory $entityRepositoryFactory, RapidApiContextProvider $contextProvider)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->crudService = $crudService;
        $this->entityRepositoryFactory = $entityRepositoryFactory;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @throws ExceptionInterface
     * @throws RapidApiCrudException
     */
    public function __invoke(UpdateEntityCommand $command): RapidApiCrudEntity
    {
        $context = $this->contextProvider->getContext();

        $entityRepository = $this->entityRepositoryFactory->createEntityRepository();
        $data = $command->data;

        $entity = $entityRepository->find($command->className, $command->id);
        $currentEntity = clone $entity;

        $event = new BeforeEntityUpdatedEvent($context, $data, $currentEntity);
        $this->eventDispatcher->dispatch($event, BeforeEntityUpdatedEvent::NAME);

        $entity = $this->crudService->arrayToEntity($data, $command->className, SerializerGroups::UPDATE, $entity);
        $this->crudService->validate($entity);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $event = new AfterEntityUpdatedEvent($context, $entity, $currentEntity);
        $this->eventDispatcher->dispatch($event, AfterEntityUpdatedEvent::NAME);

        return $entity;
    }
}
