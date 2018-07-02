<?php

namespace Norsys\LogsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('norsys_logs');

        $rootNode
            ->children()
                ->arrayNode('security')
                    ->info('Optional security configuration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Enable security to access logs')
                            ->defaultValue(true)
                        ->end()
                        ->arrayNode('allowed_ips')
                            ->prototype('scalar')
                            ->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('base_layout')
                    ->cannotBeEmpty()
                    ->defaultValue('NorsysLogsBundle::layout.html.twig')
                ->end()
                ->scalarNode('logs_per_page')
                    ->cannotBeEmpty()
                    ->defaultValue(25)
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($v) {
                            return (int) $v;
                        })
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->children()
                        ->scalarNode('table_name')->defaultValue('monolog_entries')->end()
                        ->scalarNode('connection_name')->end()
                        ->arrayNode('connection')
                            ->cannotBeEmpty()
                            ->children()
                                ->scalarNode('driver')->end()
                                ->scalarNode('driverClass')->end()
                                ->scalarNode('pdo')->end()
                                ->scalarNode('dbname')->end()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->scalarNode('port')->defaultNull()->end()
                                ->scalarNode('user')->defaultValue('root')->end()
                                ->scalarNode('password')->defaultNull()->end()
                                ->scalarNode('charset')->defaultValue('UTF8')->end()
                                ->scalarNode('path')
                                    ->info(' The filesystem path to the database file for SQLite')
                                ->end()
                                ->booleanNode('memory')
                                    ->info('True if the SQLite database should be in-memory (non-persistent)')
                                ->end()
                                ->scalarNode('unix_socket')
                                    ->info('The unix socket to use for MySQL')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function ($v) {
                    if (isset($v['doctrine']) === false) {
                        return true;
                    }

                    if (isset($v['doctrine']['connection_name']) === false
                        && isset($v['doctrine']['connection']) === false
                    ) {
                        return true;
                    }

                    return false;
                })
                ->thenInvalid('You must provide a valid "connection_name" or "connection" definition.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) {
                    if (isset($v['doctrine']) === false) {
                        return true;
                    }

                    if (isset($v['doctrine']['connection_name']) === true
                        && isset($v['doctrine']['connection']) === true
                    ) {
                        return true;
                    }

                    return false;
                })
                ->thenInvalid('You cannot specify both options "connection_name" and "connection".')
            ->end();

        return $treeBuilder;
    }
}
