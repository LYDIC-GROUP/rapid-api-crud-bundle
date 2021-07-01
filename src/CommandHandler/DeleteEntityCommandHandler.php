<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Factory\EntityRepositoryFactory;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class DeleteEntityCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private EntityRepositoryFactory $entityRepositoryFactory;

    public function __construct(EntityManagerInterface $entityManager, EntityRepositoryFactory $entityRepositoryFactory)
    {
        $this->entityManager = $entityManager;
        $this->entityRepositoryFactory = $entityRepositoryFactory;
    }

    /**
     * @throws RapidApiCrudException
     */
    public function __invoke(DeleteEntityCommand $command): void
    {
        $entityRepository = $this->entityRepositoryFactory->createEntityRepository();
        $entityRepository->delete($command->className, $command->id);
    }
}
