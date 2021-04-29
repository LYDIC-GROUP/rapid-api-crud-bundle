<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class RapidApiCrudExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // Using XML as opposed to YAML to prevent a dependency to the yaml file loader (best practice)
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');
    }
}