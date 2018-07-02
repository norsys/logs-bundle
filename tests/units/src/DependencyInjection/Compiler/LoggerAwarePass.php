<?php

namespace Norsys\LogsBundle\Tests\Units\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Norsys\LogsBundle\Tests\Units\Test;
use mock\Symfony\Component\DependencyInjection\ContainerBuilder as MockOfContainer;
use mock\Symfony\Component\DependencyInjection\Definition as MockOfDefinition;

class LoggerAwarePass extends Test
{
    public function testOnProcessMethod()
    {
        $this
            ->assert('Test on process method.')
            ->given(
                $container = new MockOfContainer,
                $this->calling($container)->findTaggedServiceIds = function($taggedServiceId) {
                    if ($this->testedClass->getClass()::REFERENCE === $taggedServiceId) {
                        return ['id_1' => ['tag_1', 'tag_2']];
                    } else {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'Service % not found for test.',
                                $taggedServiceId
                            )
                        );
                    }
                },
                $definition = new MockOfDefinition,
                $this->calling($container)->getDefinition = function ($definitionId) use ($definition) {
                    if ('id_1' === $definitionId) {
                        return $definition;
                    } else {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'Service % not found for test.',
                                $definitionId
                            )
                        );
                    }
                },
                $this->newTestedInstance()
            )
            ->if($this->testedInstance->process($container))
            ->then
                ->mock($definition)
                    ->receive('addMethodCall')
                        ->withArguments(
                            'setLogger',
                            [new Reference('logger')]
                        )
                            ->once();
    }
}
