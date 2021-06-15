<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/3/2021
 * Time: 7:23 PM
 */

namespace LydicGroup\RapidApiCrudBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;
use Symfony\Component\Security\Core\Security;

class EntityRepository implements EntityRepositoryInterface
{
    private ManagerRegistry $doctrine;
    private Security $security;

    /**
     * EntityRepository constructor.
     */
    public function __construct(ManagerRegistry $managerRegistry, Security $security)
    {
        $this->doctrine = $managerRegistry;
        $this->security = $security;
    }

    public function getQueryBuilder(string $class): QueryBuilder
    {
        $entityManager = $this->doctrine->getManagerForClass($class);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('entity')
            ->from($class, 'entity');

        return $queryBuilder;
    }

    public function getQueryBuilderAssoc(string $assocClass, string $mappedBy, string $id): QueryBuilder
    {
        $entityManager = $this->doctrine->getManagerForClass($assocClass);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('entity')
            ->from($assocClass, 'entity')
            ->where(sprintf('entity.%s = :id', $mappedBy))
            ->setParameter(':id', $id);

        return $queryBuilder;
    }

    public function find(string $class, $id): RapidApiCrudEntity
    {
        $entityManager = $this->doctrine->getManagerForClass($class);
        $repository = $entityManager->getRepository($class);

        return $repository->find($id);
    }

    public function persist($entity): void
    {
        $entityManager = $this->doctrine->getManagerForClass(get_class($entity));

        $entityManager->persist($entity);
        $entityManager->flush();
    }

    public function delete(string $class, $id): void
    {
        if (!is_array($id)) {
            $id = ['id' => $id];
        }

        $entityManager = $this->doctrine->getManagerForClass($class);
        $entity = $entityManager->find($class, $id);

        $entityManager->remove($entity);
        $entityManager->flush();
    }
}
