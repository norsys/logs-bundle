<?php

namespace Norsys\LogsBundle\Tests\Units\Model;

use Norsys\LogsBundle\Tests\Units\Test;
use mock\Doctrine\DBAL\Connection as MockOfConnection;
use mock\Doctrine\DBAL\Schema\Schema as MockOfSchema;
use mock\Doctrine\DBAL\Schema\Table as MockOfTable;
use mock\Doctrine\DBAL\Platforms\MySqlPlatform as MockOfPlateform;
use mock\Doctrine\DBAL\Schema\MySqlSchemaManager as MockOfSchemaManager;
use mock\Norsys\LogsBundle\Model\SchemaDiffFactory as MockOfSchemaDiffFactory;
use mock\Doctrine\DBAL\Schema\SchemaDiff as MockOfSchemaDiff;

class SchemaBuilder extends Test
{
    public function testOnDropMethod()
    {
        $this
            ->assert('Test the drop method.')
            ->given(
                $connection = new MockOfConnection,
                $plateform = new MockOfPlateform,
                $this->calling($connection)->getDatabasePlatform = $plateform,
                $tableName  = 'table_name',
                $table = new MockOfTable,
                $schema = new MockOfSchema,
                $this->calling($schema)->createTable = $table,
                $this->calling($schema)->toDropSql = ['query_1', 'query_2'],
                $schemaDiffFactory = new MockOfSchemaDiffFactory,
                $this->newTestedInstance($connection, $tableName, $schema, $schemaDiffFactory)
            )
            ->if($this->testedInstance->drop())
            ->then
                ->mock($schema)
                    ->receive('toDropSql')
                        ->once
                ->mock($connection)
                    ->receive('query')
                        ->withArguments('query_1')
                            ->once
                    ->receive('query')
                        ->withArguments('query_2')
                            ->once;
    }

    public function testOnCreateMethod()
    {
        $this
            ->assert('Test on create method.')
            ->given(
                $connection = new MockOfConnection,
                $plateform = new MockOfPlateform,
                $this->calling($connection)->getDatabasePlatform = $plateform,
                $tableName  = 'table_name',
                $schema = new MockOfSchema,
                $table = new MockOfTable,
                $this->calling($schema)->createTable = $table,
                $this->calling($schema)->toSql = ['query_1'],
                $schemaDiffFactory = new MockOfSchemaDiffFactory,
                $this->newTestedInstance($connection, $tableName, $schema, $schemaDiffFactory)
            )
            ->if($this->testedInstance->create())
            ->then
                ->mock($schema)
                    ->receive('toSql')
                        ->once
                ->mock($connection)
                    ->receive('query')
                        ->withArguments('query_1')
                            ->once;
    }

    public function testOnUpdateMethod()
    {
        $this
            ->assert('Test on update method.')
            ->given(
                $connection = new MockOfConnection,
                $schemaManager = new MockOfSchemaManager,
                $schema = new MockOfSchema,
                $this->calling($connection)->getSchemaManager = $schemaManager,
                $plateform = new MockOfPlateform,
                $this->calling($connection)->getDatabasePlatform = $plateform,
                $this->calling($schemaManager)->createSchema = $schema,
                $tableName  = 'table_name',
                $table = new MockOfTable,
                $this->calling($schema)->createTable = $table,
                $this->calling($schema)->getTable = $table,
                $schemaDiffFactory = new MockOfSchemaDiffFactory,
                $schemaDiff = new MockOfSchemaDiff,
                $this->calling($schemaDiffFactory)->getSchemaDiff = $schemaDiff,
                $this->calling($schemaDiff)->toSaveSql = ['query_1'],

                $this->newTestedInstance($connection, $tableName, $schema, $schemaDiffFactory)
            )
            ->if($this->testedInstance->update())
            ->then
                ->mock($connection)
                    ->receive('query')
                        ->withArguments('query_1')
                            ->once;
        ;
    }
}
