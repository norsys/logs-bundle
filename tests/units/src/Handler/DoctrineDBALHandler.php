<?php

namespace Norsys\LogsBundle\Tests\Units\Handler;

use Norsys\LogsBundle\Formatter\NormalizerFormatter;
use Norsys\LogsBundle\Tests\Units\Test;
use mock\Doctrine\DBAL\Connection as MockOfConnection;

class DoctrineDBALHandler extends Test
{
    public function testOnWriteMethod()
    {
        $this
            ->assert('Verify if insert method is used.')
            ->given(
                $record = ['level' => 101],
                $connection = new MockOfConnection,
                $tableName = 'table_name',
                $this->newTestedInstance($connection, $tableName)
            )
            ->if($result = $this->testedInstance->handle($record))
            ->then
            ->mock($connection)
            ->receive('insert')
            ->withArguments($tableName, $record)
            ->once;
    }

    public function testOnDefaultFormater()
    {
        $this
            ->assert('Check if the default formater is returned when we call the method getFormatter.')
            ->given(
                $connection = new MockOfConnection,
                $tableName = 'table_name',
                $this->newTestedInstance($connection, $tableName)
            )
            ->if($result = $this->testedInstance->getFormatter())
            ->then
            ->object($result)
            ->isInstanceOf(NormalizerFormatter::class);
    }
}
