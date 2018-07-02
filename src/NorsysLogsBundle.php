<?php

namespace Norsys\LogsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Norsys\LogsBundle\DependencyInjection\Compiler\LoggerAwarePass;

/**
 * Class NorsysLogsBundle
 */
class NorsysLogsBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LoggerAwarePass());
    }
}
