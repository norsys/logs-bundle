<?php

namespace Norsys\LogsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class LoggerAwarePass
 * Helper to set logger for the classes using LoggerAwareTrait
 */
class LoggerAwarePass implements CompilerPassInterface
{
    /**
     * @var string
     */
    const REFERENCE = 'logger.aware';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $logger = new Reference('logger');
        $taggedServices = $container->findTaggedServiceIds(self::REFERENCE);
        foreach ($taggedServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addMethodCall('setLogger', [ $logger ]);
        }
    }
}
