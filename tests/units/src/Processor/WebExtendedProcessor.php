<?php

namespace Norsys\LogsBundle\Tests\Units\Processor;

use Norsys\LogsBundle\Tests\Units\Test;

class WebExtendedProcessor extends Test
{
    public function testOnInvokeMethod()
    {
        $this
            ->assert('Test with null construction.')
            ->given(
                $this->newTestedInstance(),
                $record = ['record1' => 'record1_value']
            )
            ->if($r = $this->testedInstance->__invoke($record))
            ->then
                ->array($r)
                    ->isEqualTo($record)
            ->assert('REQUEST_URI is set.')
            ->given(
                $serverData = ['REQUEST_URI' => '128.0.0.1'],
                $this->newTestedInstance($serverData)
            )
            ->if($r = $this->testedInstance->__invoke($record))
            ->then
                ->array($r)
                    ->isEqualTo([
                        'record1' => 'record1_value',
                        'http_server' => [
                            'REQUEST_URI' => '128.0.0.1'
                        ],
                        'http_post' => [],
                        'http_get' => []
                    ]);
    }
}
