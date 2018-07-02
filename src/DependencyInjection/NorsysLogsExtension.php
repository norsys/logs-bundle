<?php

namespace Norsys\LogsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Doctrine\DBAL\DriverManager;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NorsysLogsExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('norsys_logs.security.enabled', $config['security']['enabled']);
        $container->setParameter('norsys_logs.security.allowed_ips', $config['security']['allowed_ips']);
        $container->setParameter('norsys_logs.base_layout', $config['base_layout']);
        $container->setParameter('norsys_logs.logs_per_page', $config['logs_per_page']);

        $container->setParameter('norsys_logs.doctrine.table_name', $config['doctrine']['table_name']);

        if (isset($config['doctrine']['connection_name']) === true) {
            $container->setAlias(
                'norsys_logs.doctrine_dbal.connection',
                sprintf('doctrine.dbal.%s_connection', $config['doctrine']['connection_name'])
            );
        }

        if (isset($config['doctrine']['connection']) === true) {
            $connectionDefinition = new Definition(
                'Doctrine\DBAL\Connection',
                array($config['doctrine']['connection'])
            );
            $connectionDefinition->setFactory('Doctrine\DBAL\DriverManager::getConnection');
            $container->setDefinition('norsys_logs.doctrine_dbal.connection', $connectionDefinition);
        }
    }
}
