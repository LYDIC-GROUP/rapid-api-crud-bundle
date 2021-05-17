<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\DependencyInjection;

use LydicGroup\RapidApiCrudBundle\Enum\FilterMode;
use LydicGroup\RapidApiCrudBundle\Enum\SorterMode;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class RapidApiCrudExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // Using XML as opposed to YAML to prevent a dependency to the yaml file loader (best practice)
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('LydicGroup\RapidApiCrudBundle\Dto\ControllerConfig');
        $definition->addMethodCall('setListActionEnabled', [$config['controller']['listActionEnabled'] ?? true]);
        $definition->addMethodCall('setFindActionEnabled', [$config['controller']['findActionEnabled'] ?? true]);
        $definition->addMethodCall('setCreateActionEnabled', [$config['controller']['createActionEnabled'] ?? true]);
        $definition->addMethodCall('setUpdateActionEnabled', [$config['controller']['updateActionEnabled'] ?? true]);
        $definition->addMethodCall('setDeleteActionEnabled', [$config['controller']['deleteActionEnabled'] ?? true]);
        $definition->addMethodCall('setFilterMode', [FilterMode::fromLabel($config['controller']['filterMode'] ?? 'BASIC')]);
        $definition->addMethodCall('setSorterMode', [SorterMode::fromLabel($config['controller']['sorterMode'] ?? 'BASIC')]);
    }
}