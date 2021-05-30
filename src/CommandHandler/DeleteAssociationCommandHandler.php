<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use LydicGroup\RapidApiCrudBundle\Command\DeleteAssociationCommand;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Exception\RapidApiCrudException;
use LydicGroup\RapidApiCrudBundle\Service\CrudService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;


class DeleteAssociationCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CrudService $crudService;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        EntityManagerInterface $entityManager,
        CrudService $crudService,
        PropertyAccessorInterface $propertyAccessor
    )
    {
        $this->entityManager = $entityManager;
        $this->crudService = $crudService;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @throws RapidApiCrudException
     */
    public function __invoke(DeleteAssociationCommand $command): RapidApiCrudEntity
    {
        //TODO: Centralize this duplicated logic
        $classMetadata = $this->entityManager->getClassMetadata($command->className);
        if (!$classMetadata->hasAssociation($command->assocName)) {
            throw new NotFoundException();
        }

        $entity = $this->crudService->entityById($command->className, $command->id);

        $assocClassName = $classMetadata->getAssociationTargetClass($command->assocName);
        $assocEntity = $this->crudService->entityById($assocClassName, $command->assocId);

        if ($classMetadata->isSingleValuedAssociation($command->assocName)) {
            $this->propertyAccessor->setValue($entity, $command->assocName, null);
        } else {
            /** @var PersistentCollection $associatedEntities */
            $associatedEntities = $this->propertyAccessor->getValue($entity, $command->assocName)->toArray();
            $associatedEntities = array_filter($associatedEntities, function ($associatedEntity) use ($assocEntity) {
                return $associatedEntity !== $assocEntity;
            });

            $this->propertyAccessor->setValue($entity, $command->assocName, $associatedEntities);
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }
}