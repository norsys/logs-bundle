<?php

namespace Norsys\LogsBundle\Tests\Units\Command;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Norsys\LogsBundle\Tests\Units\Test;
use mock\Symfony\Component\DependencyInjection\ContainerInterface as MockOfContainer;
use mock\Doctrine\DBAL\Connection as MockOfDoctrineConnection;
use mock\Norsys\LogsBundle\Model\SchemaBuilder as MockOfLogSchemaBuilder;
use mock\Doctrine\DBAL\Schema\SchemaDiff as MockOfSchemaDiff;
use mock\Symfony\Component\Console\Input\InputInterface as MockOfInput;
use mock\Symfony\Component\Console\Output\OutputInterface as MockOfOutput;
use mock\Doctrine\DBAL\Platforms\AbstractPlatform as MockOfPlatform;
use mock\Symfony\Component\Console\Helper\HelperSet as MockOfHelperSet;
use mock\Symfony\Component\Console\Helper\SymfonyQuestionHelper as MockOfQuestionHelper;
use mock\Norsys\LogsBundle\Model\SchemaDiffFactory as MockOfSchemaDiffFactory;

class SchemaUpdateCommand extends Test
{
    public function testOnConfigureMethod()
    {
        $this
            ->assert('Configure method is called at Command instanciation.')
            ->given($this->newTestedInstance())
            ->if($result = $this->testedInstance->getName())
            ->then
                ->string($result)
                    ->isEqualTo('norsys:logs:schema-update')
            ->if($result = $this->testedInstance->getDescription())
            ->then
                ->string($result)
                    ->isEqualTo('Update Monolog table from schema')
            ->if($definition = $this->testedInstance->getDefinition())
            ->then
                ->object($definition)
                    ->isInstanceOf(InputDefinition::class)
                ->array($options = $definition->getOptions())
                    ->hasKey('force')
                ->object($options['force'])
                    ->isEqualTo(
                        new InputOption('force', 'f', null, 'Execute queries')
                    );
    }

    public function testOnExecuteMethod()
    {
        $this
            ->assert('There is nothing to update.')
            ->given(
                $container = new MockOfContainer,
                $logSchemaBuilder = new MockOfLogSchemaBuilder,
                $schemaDiff = new MockOfSchemaDiff,
                $this->calling($schemaDiff)->toSql = [],
                $dbaConnection = new MockOfDoctrineConnection,
                $this->calling($dbaConnection)->getDatabasePlatform = new MockOfPlatform,
                $schemaDiffFactory = new MockOfSchemaDiffFactory,
                $this->calling($schemaDiffFactory)->getSchemaDiff = $schemaDiff,
                $this->calling($container)->get = function($serviceName) use ($logSchemaBuilder, $dbaConnection, $schemaDiffFactory) {
                    switch ($serviceName) {
                        case 'norsys_logs.doctrine_dbal.connection':
                            return $dbaConnection;
                        case 'norsys_logs.model.log_schema_builder':
                            return $logSchemaBuilder;
                        case 'norsys_logs.dbal.schema_diff_factory':
                            return $schemaDiffFactory;
                        default: throw new \Exception('Service not found on test.');
                    }
                },
                $this->calling($container)->getParameter = function($name) {
                    if ('norsys_logs.doctrine.table_name' === $name) {
                        return 'table_name';
                    } else {
                        throw new \Exception('Parameter not found on test.');
                    }
                },
                $input = new MockOfInput,
                $output = new MockOfOutput,
                $this->newTestedInstance(),
                $this->testedInstance->setContainer($container)
            )
            ->if($result = $this->testedInstance->run($input, $output))
            ->then
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments('Nothing to update - your database is already in sync with the current Monolog schema.')
                            ->once
                ->variable($result)
                    ->isEqualTo(0)
            ->assert('We try where there is schema diff.')
            ->given(
                $this->calling($schemaDiff)->toSql = ['1' => 'schema_diff_1', '2' => 'schema_diff_2']
            )
            ->if($result = $this->testedInstance->run($input, $output))
            ->then
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments('<comment>Warning</comment>: This operation may not be executed in a production environment')
                            ->once
                    ->receive('writeln')
                        ->withArguments('<info>SQL operations to execute to Monolog table "<comment>table_name</comment>":</info>')
                            ->once
                    ->receive('writeln')
                        ->withArguments('schema_diff_1;' . PHP_EOL . 'schema_diff_2')
                            ->once
            ->assert('We try when the force option is not enabled.')
            ->given(
                $this->calling($input)->getOption = function($optionName) {
                    if ('force' === $optionName) {
                        return false;
                    } else {
                        throw new \Exception('Option not found for test.');
                    }
                },
                $helperSet = new MockOfHelperSet,
                $questionHelper = new MockOfQuestionHelper,
                $this->calling($helperSet)->get = function($name) use ($questionHelper) {
                    if ('question' === $name) {
                        return $questionHelper;
                    } else {
                        throw new \Exception('Helper not found for the test.');
                    }
                },
                $this->newTestedInstance(),
                $this->testedInstance->setContainer($container),
                $this->testedInstance->setHelperSet($helperSet)
            )
            ->if(
                $result = $this->testedInstance->run($input, $output)
            )
            ->then
                ->mock($questionHelper)
                    ->receive('ask')
                        ->once
                ->mock($logSchemaBuilder)
                    ->receive('update')
                        ->once
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments(
                            '<info>Successfully updated Monolog table "<comment>table_name</comment>"! "2" queries ' .
                            'were executed</info>'
                        )
                            ->once
                ->variable($result)
                    ->isEqualTo(0)
            ->assert('The schema builder update method leaves a exception.')
            ->given(
                $this->calling($logSchemaBuilder)->update->throw = new \Exception('exception_message')
            )
            ->if(
                $result = $this->testedInstance->run($input, $output)
            )
            ->then
                ->mock($output)
                    ->receive('writeln')
                        ->withArguments(
                            '<error>Could not update Monolog table "<comment>table_name</comment>"...</error>'
                        )
                            ->once
                    ->receive('writeln')
                        ->withArguments(
                            '<error>exception_message</error>'
                        )
                            ->once
                    ->variable($result)
                        ->isEqualTo(1);
    }
}
