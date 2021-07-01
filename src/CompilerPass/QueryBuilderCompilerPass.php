<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/16/2021
 * Time: 1:07 AM
 */

namespace LydicGroup\RapidApiCrudBundle\CompilerPass;

use LydicGroup\RapidApiCrudBundle\Factory\CriteriaFactory;
use LydicGroup\RapidApiCrudBundle\Factory\SortFactory;
use LydicGroup\RapidApiCrudBundle\QueryBuilder\RapidApiCriteriaInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QueryBuilderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $criteriaFactoryDefinition = $container->findDefinition(CriteriaFactory::class);
        $taggedServices = $container->findTaggedServiceIds('rapid.api.criteria');

        foreach($taggedServices as $id => $tags) {
            $criteriaFactoryDefinition->addMethodCall('addCriteria', [new Reference($id)]);
        }


        $criteriaFactoryDefinition = $container->findDefinition(SortFactory::class);
        $taggedServices = $container->findTaggedServiceIds('rapid.api.sorter');

        foreach($taggedServices as $id => $tags) {
            $criteriaFactoryDefinition->addMethodCall('addSorter', [new Reference($id)]);
        }
    }
}
