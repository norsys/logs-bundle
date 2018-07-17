<?php

namespace Norsys\LogsBundle\Model;

use Doctrine\DBAL\Connection;

/**
 * Class LogRepository
 */
class LogRepository
{
    /**
     * @var Connection $conn
     */
    protected $conn;

    /**
     * @var string $tableName
     */
    private $tableName;

    /**
     * @param Connection $conn
     * @param string     $tableName
     */
    public function __construct(Connection $conn, string $tableName)
    {
        $this->conn      = $conn;
        $this->tableName = $tableName;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->conn->createQueryBuilder();
    }

    /**
     * Initialize a QueryBuilder of latest log entries.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getLogsQueryBuilder()
    {
        return $this->createQueryBuilder()
                    ->select('l.channel')
                    ->addSelect('l.level')
                    ->addSelect('l.level_name')
                    ->addSelect('l.message')
                    ->addSelect('MAX(l.id) AS id')
                    ->addSelect('MAX(l.datetime) AS datetime')
                    ->addSelect('COUNT(l.id) AS count')
                    ->from($this->tableName, 'l')
                    ->groupBy('l.channel, l.level, l.level_name, l.message')
                    ->orderBy('datetime', 'DESC');
    }

    /**
     * Retrieve a log entry by his ID.
     *
     * @param integer $id
     *
     * @return Log|null
     */
    public function getLogById(int $id)
    {
        $log = $this->createQueryBuilder()
                    ->select('l.*')
                    ->from($this->tableName, 'l')
                    ->where('l.id = :id')
                    ->setParameter(':id', $id)
                    ->execute()
                    ->fetch();

        if (false !== $log) {
            return new Log($log);
        }
    }
}
