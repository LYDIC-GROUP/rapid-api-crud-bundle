<?php

/**
 * Created by PhpStorm.
 * User: Willem Turkstra
 * Date: 5/16/2021
 * Time: 9:38 PM
 */

namespace LydicGroup\RapidApiCrudBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('rapid_api_crud');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('controller')
                    ->children()
                        ->booleanNode('listActionEnabled')->defaultTrue()->end()
                        ->booleanNode('findActionEnabled')->defaultTrue()->end()
                        ->booleanNode('createActionEnabled')->defaultTrue()->end()
                        ->booleanNode('updateActionEnabled')->defaultTrue()->end()
                        ->booleanNode('deleteActionEnabled')->defaultTrue()->end()
                        ->enumNode('filterMode')
                            ->values(['BASIC', 'EXTENDED'])
                            ->defaultValue('BASIC')
                        ->end()
                        ->enumNode('sorterMode')
                            ->values(['BASIC'])
                            ->defaultValue('BASIC')
                        ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}