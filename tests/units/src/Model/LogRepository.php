<?php

namespace Norsys\LogsBundle\Tests\Units\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Norsys\LogsBundle\Model\Log;
use Norsys\LogsBundle\Tests\Units\Test;
use mock\Doctrine\DBAL\Connection as MockOfDoctrineConnection;
use mock\Doctrine\DBAL\Query\QueryBuilder as MockOfQueryBuilder;
use mock\Doctrine\DBAL\Driver\Statement as MockOfStatement;

class LogRepository extends Test
{
    public function testOnGetLogsQueryBuilderMethod()
    {
        $this
            ->assert('Test on get logs query builder method.')
            ->given(
                $connection = new MockOfDoctrineConnection,
                $queryBuilder = new MockOfQueryBuilder,
                $this->calling($queryBuilder)->select = $queryBuilder,
                $this->calling($queryBuilder)->addSelect = $queryBuilder,
                $this->calling($queryBuilder)->from = $queryBuilder,
                $this->calling($queryBuilder)->groupBy = $queryBuilder,
                $this->calling($queryBuilder)->orderBy = $queryBuilder,
                $this->calling($connection)->createQueryBuilder = $queryBuilder,
                $tableName = 'table_name',
                $this->newTestedInstance($connection, $tableName)
            )
            ->if($r = $this->testedInstance->getLogsQueryBuilder())
            ->then
            ->object($r)
            ->isInstanceOf(QueryBuilder::class);
    }

    public function testOnGetLogById()
    {
        $this
            ->assert('Test on get log by id.')
            ->given(
                $connection = new MockOfDoctrineConnection,
                $queryBuilder = new MockOfQueryBuilder,
                $statement = new MockOfStatement,
                $this->calling($statement)->fetch = [
                    'id' => 1,
                    'channel' => 'channel_x',
                    'level' => 'level_x',
                    'level_name' => 'level_name_x',
                    'message' => 'message_x',
                    'datetime' => '2017-01-01',
                    'context' => '{"context_param1":"context_x"}',
                    'extra' => '{"extra_param1": "extra_x"}',
                    'http_server' => '{"http_server_param1": "http_server_x"}',
                    'http_post' => '{"http_post_param1": "http_post_x"}',
                    'http_get' => '{"http_get_param1": "http_get_x"}'
                ],
                $this->calling($connection)->createQueryBuilder = $queryBuilder,
                $this->calling($queryBuilder)->select = $queryBuilder,
                $this->calling($queryBuilder)->from = $queryBuilder,
                $this->calling($queryBuilder)->where = $queryBuilder,
                $this->calling($queryBuilder)->setParameter = $queryBuilder,
                $this->calling($queryBuilder)->execute = $statement,
                $tableName = 'table_name',
                $this->newTestedInstance($connection, $tableName),
                $id = 1
            )
            ->if($r = $this->testedInstance->getLogById($id))
            ->then
            ->object($r)
            ->isInstanceOf(Log::class)
            ->string($r->getMessage())
            ->isEqualTo('message_x');
    }
}
