<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 6/24/2021
 * Time: 11:32 PM
 */

namespace LydicGroup\RapidApiCrudBundle\CompilerPass;

use LydicGroup\RapidApiCrudBundle\Factory\CriteriaFactory;
use LydicGroup\RapidApiCrudBundle\Factory\EntityRepositoryFactory;
use LydicGroup\RapidApiCrudBundle\Factory\SortFactory;
use LydicGroup\RapidApiCrudBundle\Repository\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EntityRepositoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(EntityRepositoryInterface::class)->addTag('rapid.api.entity.repository');

        $definition = $container->findDefinition(EntityRepositoryFactory::class);
        $taggedServices = $container->findTaggedServiceIds('rapid.api.entity.repository');

        foreach($taggedServices as $id => $tags) {
            $definition->addMethodCall('addEntityRepository', [new Reference($id)]);
        }
    }
}
