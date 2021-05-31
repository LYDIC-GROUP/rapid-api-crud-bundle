<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Command\FindEntityCommand;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class FindEntityCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws NotFoundException
     */
    public function __invoke(FindEntityCommand $command): RapidApiCrudEntity
    {
        $entity = $this->entityManager->find($command->className, $command->id);
        if (!$entity instanceof RapidApiCrudEntity) {
            throw new NotFoundException();
        }

        return $entity;
    }
}