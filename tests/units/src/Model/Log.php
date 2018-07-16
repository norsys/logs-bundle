<?php

namespace Norsys\LogsBundle\Tests\Units\Model;

use Norsys\LogsBundle\Tests\Units\Test;

class Log extends Test
{
    public function testAccessor()
    {
        $this
            ->assert('Test accessors on full data case.')
            ->given(
                $data = [
                    'id' => 'id_x',
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
                $this->newTestedInstance($data)
            )
            ->if($r = $this->testedInstance->getId())
            ->then
            ->string($r)
            ->isEqualTo('id_x')
            ->if($r = $this->testedInstance->getChannel())
            ->then
            ->string($r)
            ->isEqualTo('channel_x')
            ->if($r = $this->testedInstance->getLevel())
            ->then
            ->string($r)
            ->isEqualTo('level_x')
            ->if($r = $this->testedInstance->getLevelName())
            ->then
            ->string($r)
            ->isEqualTo('level_name_x')
            ->if($r = $this->testedInstance->getMessage())
            ->then
            ->string($r)
            ->isEqualTo('message_x')
            ->if($r = $this->testedInstance->getDate())
            ->then
            ->object($r)
            ->isInstanceOf(\DateTime::class)
            ->if($r = $this->testedInstance->getContext())
            ->then
            ->array($r)
            ->string['context_param1']
            ->isEqualTo('context_x')
            ->if($r = $this->testedInstance->getExtra())
            ->then
            ->array($r)
            ->string['extra_param1']
            ->isEqualTo('extra_x')
            ->if($r = $this->testedInstance->getServerData())
            ->then
            ->array($r)
            ->string['http_server_param1']
            ->isEqualTo('http_server_x')
            ->if($r = $this->testedInstance->getPostData())
            ->then
            ->array($r)
            ->string['http_post_param1']
            ->isEqualTo('http_post_x')
            ->if($r = $this->testedInstance->getGetData())
            ->then
            ->array($r)
            ->string['http_get_param1']
            ->isEqualTo('http_get_x')
            ->if($r = $this->testedInstance->__toString())
            ->then
            ->string($r)
            ->isEqualTo('message_x');

        $this
            ->assert('Message is long, so he is truncated.')
            ->given(
                $data = [
                    'id' => 'id_x',
                    'channel' => 'channel_x',
                    'level' => 'level_x',
                    'level_name' => 'level_name_x',
                    'message' => str_repeat('message_x', 15),
                    'datetime' => '2017-01-01',
                    'context' => '{"context_param1":"context_x"}',
                    'extra' => '{"extra_param1": "extra_x"}',
                    'http_server' => '{"http_server_param1": "http_server_x"}',
                    'http_post' => '{"http_post_param1": "http_post_x"}',
                    'http_get' => '{"http_get_param1": "http_get_x"}'
                ],
                $this->newTestedInstance($data)
            )
            ->if($r = $this->testedInstance->__toString())
            ->then
            ->string($r)
            ->isEqualTo(
                'message_xmessage_xmessage_xmessage_xmessage_xmessage_xmessage_xmessage_xmessage_xmessage_xmessage_xm...'
            );

        $this
            ->assert('No id is given on the data array, it trigger a exception.')
            ->given(
                $data = [
                    'id' => null,
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
                ]
            )
            ->exception(
                function () use ($data) {
                    $this->newTestedInstance($data);
                }
            )->isInstanceOf(\InvalidArgumentException::class);
    }
}
