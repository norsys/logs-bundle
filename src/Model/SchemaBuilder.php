<?php

namespace Norsys\LogsBundle\Model;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class SchemaBuilder
 */
class SchemaBuilder
{
    /**
     * @var Connection $conn
     */
    protected $conn;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var Schema $schema
     */
    protected $schema;

    /**
     * @var SchemaDiffFactory
     */
    private $schemaDiffFactory;

    /**
     * SchemaBuilder constructor.
     *
     * @param Connection        $conn
     * @param string            $tableName
     * @param Schema            $schema
     * @param SchemaDiffFactory $schemaDiffFactory
     */
    public function __construct(
        Connection $conn,
        string $tableName,
        Schema $schema,
        SchemaDiffFactory $schemaDiffFactory
    ) {
        $this->conn      = $conn;
        $this->tableName = $tableName;

        $this->schema = $schema;

        $entryTable = $this->schema->createTable($this->tableName);
        $entryTable->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $entryTable->addColumn('channel', 'string', array('length' => 255, 'notNull' => true));
        $entryTable->addColumn('level', 'integer', array('notNull' => true));
        $entryTable->addColumn('level_name', 'string', array('length' => 255, 'notNull' => true));
        $entryTable->addColumn('message', 'text', array('notNull' => true));
        $entryTable->addColumn('datetime', 'datetime', array('notNull' => true));
        $entryTable->addColumn('context', 'text');
        $entryTable->addColumn('extra', 'text');
        $entryTable->addColumn('http_server', 'text');
        $entryTable->addColumn('http_post', 'text');
        $entryTable->addColumn('http_get', 'text');
        $entryTable->setPrimaryKey(array('id'));

        $this->schemaDiffFactory = $schemaDiffFactory;
        $this->schemaDiffFactory->setTableName($this->tableName);
    }

    /**
     * @param \Closure|null $logger
     */
    public function drop(\Closure $logger = null)
    {
        $queries = $this->schema->toDropSql($this->conn->getDatabasePlatform());

        $this->executeQueries($queries, $logger);
    }

    /**
     * @param \Closure|null $logger
     */
    public function create(\Closure $logger = null)
    {
        $queries = $this->schema->toSql($this->conn->getDatabasePlatform());

        $this->executeQueries($queries, $logger);
    }

    /**
     * @param \Closure|null $logger
     */
    public function update(\Closure $logger = null)
    {
        $queries = $this->schemaDiffFactory->getSchemaDiff()->toSaveSql(
            $this->conn->getDatabasePlatform()
        );

        $this->executeQueries($queries, $logger);
    }

    /**
     * @param array         $queries
     * @param \Closure|null $logger
     *
     * @throws \Exception
     */
    protected function executeQueries(array $queries, \Closure $logger = null)
    {
        $this->conn->beginTransaction();

        try {
            foreach ($queries as $query) {
                if (null !== $logger) {
                    $logger($query);
                }

                $this->conn->query($query);
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
