<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Enum\SerializerGroups;
use LydicGroup\RapidApiCrudBundle\Event\AfterEntityCreatedEvent;
use LydicGroup\RapidApiCrudBundle\Event\BeforeEntityCreatedEvent;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Factory\EntityRepositoryFactory;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class CreateEntityCommandHandler implements MessageHandlerInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private RapidApiContextProvider $contextProvider;
    private EntityRepositoryFactory $entityRepositoryFactory;
    private CrudService $crudService;

    public function __construct(EventDispatcherInterface $eventDispatcher, RapidApiContextProvider $contextProvider, EntityRepositoryFactory $entityRepositoryFactory, CrudService $crudService)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->contextProvider = $contextProvider;
        $this->entityRepositoryFactory = $entityRepositoryFactory;
        $this->crudService = $crudService;
    }

    /**
     * @throws ExceptionInterface
     * @throws RapidApiCrudException
     */
    public function __invoke(CreateEntityCommand $command): RapidApiCrudEntity
    {
        $context = $this->contextProvider->getContext();

        $data = $command->data;

        $event = new BeforeEntityCreatedEvent($context, $data);
        $this->eventDispatcher->dispatch($event, BeforeEntityCreatedEvent::NAME);

        $entity = $this->crudService->arrayToEntity($data, $command->className, SerializerGroups::CREATE);

        $this->crudService->validate($entity);

        $entityRepository = $this->entityRepositoryFactory->createEntityRepository();
        $entityRepository->persist($entity);

        $event = new AfterEntityCreatedEvent($context, $entity);
        $this->eventDispatcher->dispatch($event, AfterEntityCreatedEvent::NAME);

        return $entity;
    }
}
