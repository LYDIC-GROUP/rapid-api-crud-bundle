<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class DeleteEntityCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private EntityRepositoryInterface $entityRepository;

    public function __construct(EntityManagerInterface $entityManager, EntityRepositoryInterface $entityRepository)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @throws RapidApiCrudException
     */
    public function __invoke(DeleteEntityCommand $command): void
    {
        $entity = $this->entityRepository->find($command->className, $command->id);

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}