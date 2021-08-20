<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\EventManager\Event;
use LydicGroup\RapidApiCrudBundle\Command\FindEntityCommand;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Event\FoundEntityEvent;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FindEntityCommandHandler implements MessageHandlerInterface
{
    private EntityRepositoryInterface $entityRepository;
    private EventDispatcherInterface $eventDispatcher;
    private RapidApiContextProvider $contextProvider;

    public function __construct(EntityRepositoryInterface $entityRepository, EventDispatcherInterface $eventDispatcher, RapidApiContextProvider $contextProvider)
    {
        $this->entityRepository = $entityRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @throws NotFoundException
     */
    public function __invoke(FindEntityCommand $command): RapidApiCrudEntity
    {
        $context = $this->contextProvider->getContext();

        $entity = $this->entityRepository->find($command->className, $command->id);
        if (!$entity instanceof RapidApiCrudEntity) {
            throw new NotFoundException();
        }

        $event = new FoundEntityEvent($context, $entity);
        $this->eventDispatcher->dispatch($event, FoundEntityEvent::NAME);

        return $entity;
    }
}
