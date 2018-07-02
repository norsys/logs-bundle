<?php

namespace Norsys\LogsBundle\Model;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Connection;

/**
 * This service give you the difference between a schema A And B for a given table.
 */
class SchemaDiffFactory
{
    /**
     * @var null|string
     */
    private $tableName;
    /**
     * @var SchemaDiff
     */
    private $schemaDiff;
    /**
     * @var Comparator
     */
    private $comparator;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var Schema
     */
    private $schema;

    /**
     * SchemaDiffFactory constructor.
     *
     * @param SchemaDiff $schemaDiff
     * @param Comparator $comparator
     * @param Connection $connection
     * @param Schema     $schema
     */
    public function __construct(
        SchemaDiff $schemaDiff,
        Comparator $comparator,
        Connection $connection,
        Schema $schema
    ) {
        $this->schemaDiff = $schemaDiff;
        $this->comparator = $comparator;
        $this->connection = $connection;
        $this->schema = $schema;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function setTableName(string $tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return SchemaDiff
     * @throws \Exception
     */
    public function getSchemaDiff()
    {
        if (null === $this->tableName) {
            throw new \Exception('You need first to inform the object about the table name.');
        }

        $tableDiff = $this->comparator->diffTable(
            $this->connection->getSchemaManager()->createSchema()->getTable($this->tableName),
            $this->schema->getTable($this->tableName)
        );

        if (false !== $tableDiff) {
            $this->schemaDiff->changedTables[$this->tableName] = $tableDiff;
        }

        return $this->schemaDiff;
    }
}
