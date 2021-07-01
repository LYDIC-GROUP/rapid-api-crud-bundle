<?php
declare(strict_types=1);

namespace LydicGroup\RapidApiCrudBundle;

use LydicGroup\RapidApiCrudBundle\CompilerPass\QueryBuilderCompilerPass;
use LydicGroup\RapidApiCrudBundle\CompilerPass\EntityRepositoryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RapidApiCrudBundle extends Bundle
{
    const CONTEXT_ATTRIBUTE_NAME = 'rapid_api_context';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new QueryBuilderCompilerPass());
        $container->addCompilerPass(new EntityRepositoryCompilerPass());
    }
}
