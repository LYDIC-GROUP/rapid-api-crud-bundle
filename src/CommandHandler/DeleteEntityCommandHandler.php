<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\EventManager\Event;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Event\AfterEntityDeletedEvent;
use LydicGroup\RapidApiCrudBundle\Event\BeforeEntityDeletedEvent;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Factory\EntityRepositoryFactory;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteEntityCommandHandler implements MessageHandlerInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private RapidApiContextProvider $contextProvider;
    private EntityManagerInterface $entityManager;
    private EntityRepositoryFactory $entityRepositoryFactory;

    public function __construct(EventDispatcherInterface $eventDispatcher, RapidApiContextProvider $contextProvider, EntityManagerInterface $entityManager, EntityRepositoryFactory $entityRepositoryFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->contextProvider = $contextProvider;
        $this->entityManager = $entityManager;
        $this->entityRepositoryFactory = $entityRepositoryFactory;
    }

    /**
     * @throws RapidApiCrudException
     */
    public function __invoke(DeleteEntityCommand $command): void
    {
        $context = $this->contextProvider->getContext();
        /** @var RapidApiCrudEntity $entity */
        $entity = $this->entityManager->find($command->className, $command->id);

        if($entity === null) {
            throw new NotFoundException(sprintf('Entity %s %s is not found!', $command->className, $command->id));
        }

        $event = new BeforeEntityDeletedEvent($context, $entity);
        $this->eventDispatcher->dispatch($event, BeforeEntityDeletedEvent::NAME);

        $entityRepository = $this->entityRepositoryFactory->createEntityRepository();
        $entityRepository->delete($command->className, $command->id);

        $event = new AfterEntityDeletedEvent($context, $entity);
        $this->eventDispatcher->dispatch($event, AfterEntityDeletedEvent::NAME);
    }
}
