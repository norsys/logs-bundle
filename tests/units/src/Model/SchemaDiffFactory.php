<?php

namespace Norsys\LogsBundle\Tests\Units\Model;

use Norsys\LogsBundle\Tests\Units\Test;

use mock\Doctrine\DBAL\Schema\Comparator as MockOfComparator;
use mock\Doctrine\DBAL\Schema\Schema as MockOfSchema;
use mock\Doctrine\DBAL\Schema\SchemaDiff as MockOfSchemaDiff;
use mock\Doctrine\DBAL\Connection as MockOfConnection;
use mock\Doctrine\DBAL\Schema\MySqlSchemaManager as MockOfSchemaManager;
use mock\Doctrine\DBAL\Schema\Table as MockOfTable;
use mock\Doctrine\DBAL\Schema\TableDiff as MockOfTableDiff;

class SchemaDiffFactory extends Test
{
    public function testOnGetSchemaDiffMethod()
    {
        $this
            ->assert('The table name is not set.')
            ->given(
                $schemaDiff = new MockOfSchemaDiff,
                $comparator = new MockOfComparator,
                $this->calling($comparator)->diffTable = false,
                $connection = new MockOfConnection,
                $schemaManager = new MockOfSchemaManager,
                $schema = new MockOfSchema,
                $table = new MockOfTable,
                $schema = new MockOfSchema,
                $this->calling($schema)->getTable = $table,
                $this->calling($schemaManager)->createSchema = $schema,
                $this->calling($connection)->getSchemaManager = $schemaManager,
                $this->newTestedInstance($schemaDiff, $comparator, $connection, $schema),
                $tableName = 'table_name'
            )
            ->exception(function() {
                $this->testedInstance->getSchemaDiff();
            })->isInstanceOf(\Exception::class)
            ->assert('The comparator found no diff.')
            ->given(
                $this->testedInstance->setTableName($tableName)
            )
            ->if($r = $this->testedInstance->getSchemaDiff())
            ->then
                ->object($r)
                    ->isInstanceOf(\Doctrine\DBAL\Schema\SchemaDiff::class)
            ->assert('The comparator found a diff.')
            ->given(
                $tableDiff = new MockOfTableDiff,
                $this->calling($comparator)->diffTable = $tableDiff
            )
            ->if($r = $this->testedInstance->getSchemaDiff())
            ->then
                ->object($r)
                    ->isInstanceOf(\Doctrine\DBAL\Schema\SchemaDiff::class)
                ->object($r->changedTables[$tableName])
                    ->isInstanceOf(\Doctrine\DBAL\Schema\TableDiff::class);
    }
}
