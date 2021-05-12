<?php
/**
 * Created by PhpStorm.
 * User: Willem
 * Date: 2/8/2021
 * Time: 9:47 PM
 */
namespace LydicGroup\RapidApiCrudBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use LydicGroup\RapidApiCrudBundle\Entity\RapidApiCrudEntity;

/**
 * Interface EntityRepositoryInterface
 * @package App\Repository
 */
interface EntityRepositoryInterface
{
    public function getQueryBuilder(string $class): QueryBuilder;

    public function find(string $class, $id): RapidApiCrudEntity;

    public function persist($entity): void;

    public function delete(string $class, $id): void;
}