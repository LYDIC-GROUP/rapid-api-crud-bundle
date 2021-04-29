<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\DeleteEntityCommand;


class DeleteEntityCommandHandler
{
    private EntityManagerInterface $entityManager;
    private CrudService $crudService;

    public function __construct(EntityManagerInterface $entityManager, CrudService $crudService)
    {
        $this->entityManager = $entityManager;
        $this->crudService = $crudService;
    }

    /**
     * @throws \LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException
     */
    public function __invoke(DeleteEntityCommand $command): void
    {
        $entity = $this->crudService->entityById($command->className, $command->id);
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}