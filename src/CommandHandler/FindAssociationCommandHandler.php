<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\CommandHandler;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use LydicGroup\RapidApiCrudBundle\Command\FindAssociationCommand;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use LydicGroup\RapidApiCrudBundle\Exception\NotFoundException;
use LydicGroup\RapidApiCrudBundle\Factory\SortFactory;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use LydicGroup\RapidApiCrudBundle\Factory\CriteriaFactory;

class FindAssociationCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private PropertyAccessorInterface $propertyAccessor;

    private EntityRepositoryInterface $entityRepository;
    private RapidApiContextProvider $contextProvider;
    private CriteriaFactory $criteriaFactory;
    private SortFactory $sortFactory;

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor, EntityRepositoryInterface $entityRepository, RapidApiContextProvider $contextProvider, CriteriaFactory $criteriaFactory, SortFactory $sortFactory) {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;

        $this->entityRepository = $entityRepository;
        $this->contextProvider = $contextProvider;
        $this->criteriaFactory = $criteriaFactory;
        $this->sortFactory = $sortFactory;
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
            $context = $this->contextProvider->getContext();

            $criteria = $this->criteriaFactory->create($context);
            $sorter = $this->sortFactory->create($context);

            $assocClass = $classMetadata->getAssociationTargetClass($command->associationName);
            $mappedBy = $classMetadata->getAssociationMappedByTargetField($command->associationName);
            $queryBuilder = $this->entityRepository->getQueryBuilderAssoc($assocClass, $mappedBy, $command->id);

            $queryBuilder = $criteria->get($queryBuilder);
            $queryBuilder = $sorter->get($queryBuilder);

            $page = (int)$context->getRequest()->query->get('page', 1);
            $limit = (int)$context->getRequest()->query->get('limit', 10);

            //Set paging limits
            $queryBuilder->setFirstResult(($page - 1) * $limit);
            $queryBuilder->setMaxResults($limit);

            return new Paginator($queryBuilder);
        }
    }
}
