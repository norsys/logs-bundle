<?php

namespace Norsys\LogsBundle\Tests\Units\Command;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Norsys\LogsBundle\Tests\Units\Test;
use mock\Symfony\Component\DependencyInjection\ContainerInterface as MockOfContainer;
use mock\Symfony\Component\Console\Input\InputInterface as MockOfInput;
use mock\Symfony\Component\Console\Output\OutputInterface as MockOfOutput;
use mock\Norsys\LogsBundle\Model\SchemaBuilder as MockOfLogSchemaBuilder;

class SchemaCreateCommand extends Test
{
    public function testOnConfigureMethod()
    {
        $this
            ->assert('Configure method is called at Command instanciation.')
            ->given($this->newTestedInstance())
            ->if($result = $this->testedInstance->getName())
            ->then
                ->string($result)
                    ->isEqualTo('norsys:logs:schema-create')
            ->if($result = $this->testedInstance->getDescription())
            ->then
                ->string($result)
                    ->isEqualTo('Create schema to log Monolog entries')
            ->if($definition = $this->testedInstance->getDefinition())
            ->then
                ->object($definition)->isInstanceOf(InputDefinition::class)
                ->array($options = $definition->getOptions())
                    ->hasKey('force')
                ->object($options['force'])
                    ->isEqualTo(
                        new InputOption('force', 'f', null, 'Execute queries')
                    );
    }

    public function testOnExecuteMethodWithForceOptionActivated()
    {
        $this
            ->assert('Option force is selected.')
            ->given(
                $input = new MockOfInput,
                $this->calling($input)->getOption = function($name) {
                    if ($name === 'force' ) {
                        return true;
                    } else {
                        throw new \Exception('Option not found in test.');
                    }
                },
                $output = new MockOfOutput,
                $this->newTestedInstance(),
                $container = new MockOfContainer,
                $logSchemaBuilder = new MockOfLogSchemaBuilder,
                $this->calling($container)->get = function($serviceId) use ($logSchemaBuilder) {
                    if ('norsys_logs.model.log_schema_builder' === $serviceId) {
                        return $logSchemaBuilder;
                    } else {
                        throw new \Exception(sprintf('Service %s not found.', $serviceId));
                    }
                },
                $this->testedInstance->setContainer($container),

                $this->calling($container)->getParameter = function($name) {
                    if ('norsys_logs.doctrine.table_name' === $name) {
                        return 'table_name';
                    } else {
                        throw new \Exception('Parameter %s not found.', $name);
                    }
                }
            )
            ->if($result = $this->testedInstance->run($input, $output))
            ->then
                ->mock($logSchemaBuilder)
                    ->receive('drop')
                        ->once
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments(
                            '<info>Dropped table <comment>table_name</comment> for Doctrine Monolog connection.</info>'
                        )
                            ->once
            ->assert('The drop operation returns a exception.')
            ->given(
                $this->calling($logSchemaBuilder)->drop = function() {
                    throw new \Exception();
                }
            )
            ->if($result = $this->testedInstance->run($input, $output))
            ->then
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments(
                            '<info>Dropped table ignored for Doctrine Monolog connection (not necessary).</info>'
                        )
                            ->once;
    }

    public function testOnExecuteMethodWithForceOptionNotActivated()
    {
        $this
            ->assert('Option force is not selected.')
            ->given(
                $input = new MockOfInput,
                $this->calling($input)->getOption = function($name) {
                    if ($name === 'force' ) {
                        return false;
                    } else {
                        throw new \Exception('ok');
                    }
                },
                $output = new MockOfOutput,
                $this->newTestedInstance(),
                $container = new MockOfContainer,
                $logSchemaBuilder = new MockOfLogSchemaBuilder,
                $this->calling($container)->get = function($serviceId) use ($logSchemaBuilder) {
                    if ('norsys_logs.model.log_schema_builder' === $serviceId) {
                        return $logSchemaBuilder;
                    } else {
                        throw new \Exception(sprintf('Service %s not found.', $serviceId));
                    }
                },
                $this->testedInstance->setContainer($container),

                $this->calling($container)->getParameter = function($name) {
                    if ('norsys_logs.doctrine.table_name' === $name) {
                        return 'table_name';
                    } else {
                        throw new \Exception('Parameter %s not found.', $name);
                    }
                }
            )
            ->if($result = $this->testedInstance->run($input, $output))
            ->then
                ->mock($logSchemaBuilder)
                    ->receive('create')
                        ->once
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments(
                            '<info>Created table <comment>table_name</comment> for Doctrine Monolog connection.</info>'
                        )
                            ->once
            ->assert('The create operation returns a exception.')
            ->given(
                $this->calling($logSchemaBuilder)->create = function() {
                    throw new \Exception();
                }
            )
            ->if($result = $this->testedInstance->run($input, $output))
            ->then
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments(
                            '<error>Could not create table <comment>table_name</comment> for Doctrine Monolog connection.</error>'
                        )
                            ->once;
    }
}
