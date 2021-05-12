<?php
/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/11/2021
 * Time: 10:06 PM
 */
namespace LydicGroup\RapidApiCrudBundle\QueryBuilder;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

interface RapidApiSortInterface
{
    public function get(QueryBuilder $queryBuilder): QueryBuilder;
}