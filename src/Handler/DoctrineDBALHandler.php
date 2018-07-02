<?php

namespace Norsys\LogsBundle\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

use Doctrine\DBAL\Connection;

use Norsys\LogsBundle\Processor\WebExtendedProcessor;
use Norsys\LogsBundle\Formatter\NormalizerFormatter;

/**
 * Handler to send messages to a database through Doctrine DBAL.
 */
class DoctrineDBALHandler extends AbstractProcessingHandler
{
    /**
     * @var Connection $connection
     */
    private $connection;

    /**
     * @var string $tableName
     */
    private $tableName;

    /**
     * @param Connection $connection
     * @param string     $tableName
     * @param integer    $level
     * @param boolean    $bubble
     */
    public function __construct(
        Connection $connection,
        string $tableName,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->connection = $connection;
        $this->tableName  = $tableName;

        parent::__construct($level, $bubble);

        $this->pushProcessor(new WebProcessor());
        $this->pushProcessor(new WebExtendedProcessor());
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $record = $record['formatted'];

        try {
            $this->connection->insert($this->tableName, $record);
        } catch (\Exception $e) {
            // Not fatal error on bad error
        }
    }

    /**
     * @return NormalizerFormatter
     */
    protected function getDefaultFormatter()
    {
        return new NormalizerFormatter();
    }
}
