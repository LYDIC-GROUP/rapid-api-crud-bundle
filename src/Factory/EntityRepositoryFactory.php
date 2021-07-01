<?php
/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 6/23/2021
 * Time: 11:34 PM
 */

namespace LydicGroup\RapidApiCrudBundle\Factory;

use Doctrine\ORM\EntityNotFoundException;
use LydicGroup\RapidApiCrudBundle\Context\RapidApiContext;
use LydicGroup\RapidApiCrudBundle\Provider\RapidApiContextProvider;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;

class EntityRepositoryFactory
{
    private RapidApiContextProvider $contextProvider;
    private array $entityRepositories;
    private ?EntityRepositoryInterface $defaultEntityRepository;

    /**
     * EntityRepositoryFactory constructor.
     * @param RapidApiContextProvider $contextProvider
     */
    public function __construct(RapidApiContextProvider $contextProvider)
    {
        $this->contextProvider = $contextProvider;
        $this->defaultEntityRepository = null;
    }

    public function addEntityRepository(EntityRepositoryInterface $entityRepository): EntityRepositoryFactory
    {
        $shortClassName = substr(strrchr(get_class($entityRepository), '\\'), 1);
        if ($shortClassName === 'EntityRepository') {
            $this->defaultEntityRepository = $entityRepository;
        } else {
            $this->entityRepositories[$shortClassName] = $entityRepository;
        }

        return $this;
    }

    public function createEntityRepository(): EntityRepositoryInterface
    {
        /** @var RapidApiContext $context */
        $context = $this->contextProvider->getContext();
        $className = $context->getEntityClassName();
        $shortName = substr(strrchr($className, '\\'), 1);

        if (!empty($this->entityRepositories[ucfirst($shortName) . 'EntityRepository'])) {
            return $this->entityRepositories[$shortName . 'EntityRepository'];
        }

        return $this->defaultEntityRepository;
    }
}
