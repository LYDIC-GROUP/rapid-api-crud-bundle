<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use LydicGroup\RapidApiCrudBundle\Command\CreateEntityCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class CreateEntityCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CrudService $crudService;

    public function __construct(EntityManagerInterface $entityManager, CrudService $crudService)
    {
        $this->entityManager= $entityManager;
        $this->crudService = $crudService;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException
     */
    public function __invoke(CreateEntityCommand $command): void
    {
        $data = $command->data;

        $entity = $this->crudService->denormalize($data, $command->className, 'create');

        $this->crudService->validate($entity);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}