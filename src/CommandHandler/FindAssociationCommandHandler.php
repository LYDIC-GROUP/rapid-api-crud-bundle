<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use LydicGroup\RapidApiCrudBundle\Command\FindAssociationCommand;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;


class FindAssociationCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor) {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @throws NotFoundException
     * @return RapidApiCrudEntity|array
     */
    public function __invoke(FindAssociationCommand $command)
    {
        $classMetadata = $this->entityManager->getClassMetadata($command->className);
        if (!$classMetadata->hasAssociation($command->associationName)) {
            throw new NotFoundException();
        }

        $entity = $this->entityManager->find($command->className, $command->id);
        if (!$entity instanceof RapidApiCrudEntity) {
            throw new NotFoundException();
        }

        if ($classMetadata->isSingleValuedAssociation($command->associationName)) {
            $associatedEntity = $this->propertyAccessor->getValue($entity, $command->associationName);
            if (empty($associatedEntity)) {
                throw new NotFoundException();
            }

            return $associatedEntity;
        } else {
            /** @var Collection $associatedEntities */
            $associatedEntities = $this->propertyAccessor->getValue($entity, $command->associationName);
            if (empty($associatedEntities)) {
                throw new NotFoundException();
            }

            return $associatedEntities->toArray();
        }
    }
}