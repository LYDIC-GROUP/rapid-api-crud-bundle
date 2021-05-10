<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\UpdateEntityCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


class UpdateEntityCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CrudService $crudService;

    public function __construct(EntityManagerInterface $entityManager, CrudService $crudService)
    {
        $this->entityManager = $entityManager;
        $this->crudService = $crudService;
    }

    /**
     * @throws ExceptionInterface
     * @throws RapidApiCrudException
     */
    public function __invoke(UpdateEntityCommand $command): void
    {
        $data = $command->data;

        $entity = $this->crudService->entityById($command->className, $command->id);
        $entity = $this->crudService->arrayToEntity($data, $command->className, 'update', $entity);

        $this->crudService->validate($entity);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}