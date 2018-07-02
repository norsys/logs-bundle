<?php

namespace Norsys\LogsBundle\Tests\Units\Controller;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Norsys\LogsBundle\Model\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Norsys\LogsBundle\Tests\Units\Test;
use mock\Symfony\Component\HttpFoundation\Request as MockOfRequest;
use mock\Symfony\Component\DependencyInjection\ContainerInterface as MockOfContainer;
use mock\Norsys\LogsBundle\Model\LogRepository as MockOfLogsModelRepository;
use mock\Knp\Component\Pager\Paginator as MockOfKnpPaginator;
use mock\Symfony\Component\HttpFoundation\ParameterBag as MockOfParameterBag;
use mock\Doctrine\DBAL\Query\QueryBuilder as MockOfQueryBuilder;
use mock\Knp\Component\Pager\Pagination\PaginationInterface as MockOfPagination;
use mock\Twig_Environment as MockOfTwig;
use mock\Norsys\LogsBundle\Model\Log as MockOfLog;

class DefaultController extends Test
{
    public function testOnIndexActionMethod()
    {
        $this
            ->assert('Test if client has access.')
            ->given(
                $request = new MockOfRequest,
                $parameterBag = new MockOfParameterBag,
                $this->calling($parameterBag)->get = function($argument) {
                    if('page' === $argument) {
                        return 1;
                    } else {
                        throw new \Exception(sprintf('argument %s not handled.', $argument));
                    }
                },
                $request->query = $parameterBag,

                $this->newTestedInstance(),
                $container = new MockOfContainer,

                // Service container definition.
                $logsModelRepository = new MockOfLogsModelRepository,
                $this->calling($logsModelRepository)->getLogsQueryBuilder = new MockOfQueryBuilder,
                $knpPaginator = new MockOfKnpPaginator,
                $this->calling($knpPaginator)->paginate = new MockOfPagination,
                $twig = new MockOfTwig,

                $this->calling($container)->get = function($serviceName) use (
                    $logsModelRepository,
                    $knpPaginator,
                    $twig
                ) {
                    switch ($serviceName) {
                        case 'norsys_logs.model.log_repository':
                            return $logsModelRepository;
                        case 'knp_paginator':
                            return $knpPaginator;
                        case 'twig':
                            return $twig;
                        default: throw new \Exception(
                            sprintf(
                                'Service "%s" not found for get method.',
                                $serviceName
                            )
                        );
                    }
                },
                $viewName = 'NorsysLogsBundle:Default:index.html.twig',
                $this->calling($twig)->render = function($view, $parameters) use ($viewName) {
                    $this->string($view)
                        ->isEqualTo($viewName);
                    $this->array($parameters)
                        ->hasKeys(['pagination', 'base_layout']);
                    $this->object($parameters['pagination'])
                        ->isInstanceOf(PaginationInterface::class);
                    $this->variable($parameters['base_layout'])
                        ->isNull;
                },
                $this->calling($container)->has = function($serviceName) use ($twig) {
                    switch ($serviceName) {
                        case 'twig':
                            return true;
                        case 'templating':
                            return false;
                        default:
                            throw new \Exception(
                                sprintf(
                                    'service "%s" not found for has method.',
                                    $serviceName
                                )
                            );
                    }
                },
                $this->testedInstance->setContainer($container)
            )
            ->if($result = $this->testedInstance->indexAction($request))
            ->then
                ->object($result)
                    ->isInstanceOf(Response::class);

        $this
            ->assert('Client do not have access.')
            ->given(
                $container = new MockOfContainer,
                $this->calling($container)->getParameter = function($name) {
                    switch($name) {
                        case 'norsys_logs.security.enabled':
                            return true;
                        case 'norsys_logs.security.allowed_ips':
                            return ['128.0.0.1'];
                        default:
                            throw new \Exception('Parameter for container not handled in test.');
                    }
                },
                $request = new MockOfRequest,
                $this->calling($request)->getClientIp = '128.0.0.2',
                $this->newTestedInstance(),
                $this->testedInstance->setContainer($container),
                $logsModelRepository = new MockOfLogsModelRepository,
                $this->calling($container)->get = function($serviceName) use (
                    $logsModelRepository
                ) {
                switch ($serviceName) {
                    case 'norsys_logs.model.log_repository':
                        return $logsModelRepository;
                    default:
                        throw new \Exception(
                            sprintf(
                                'Service "%s" not found for get method.',
                                $serviceName
                            )
                        );
                    }
                }
            )
            ->exception(
                function() use ($request) {
                    $this->testedInstance->indexAction($request);
            })->isInstanceOf(NotFoundHttpException::class);
    }

    public function testOnShowAction()
    {
        $this
            ->assert('Test on showAction method if client has access.')
            ->given(
                $container = new MockOfContainer,
                $logsModelRepository = new MockOfLogsModelRepository,
                $this->calling($logsModelRepository)->getLogById = new MockOfLog,
                $twig = new MockOfTwig,
                $this->calling($container)->get = function($serviceName) use ($logsModelRepository, $twig) {
                    switch ($serviceName) {
                        case 'norsys_logs.model.log_repository':
                            return $logsModelRepository;
                        case 'twig':
                            return $twig;
                        default: throw new \Exception(
                            'Service "%s" not found for get method.',
                            $serviceName
                        );
                    }
                },
                $this->calling($container)->has = function($serviceName) use ($twig) {
                    switch ($serviceName) {
                        case 'twig':
                            return true;
                        case 'templating':
                            return false;
                        default:
                            throw new \Exception(
                                sprintf(
                                    'service "%s" not found for has method.',
                                    $serviceName
                                )
                            );
                    }
                },
                $viewName = 'NorsysLogsBundle:Default:show.html.twig',
                $this->calling($twig)->render = function($view, $parameters) use ($viewName) {
                    $this->string($view)
                        ->isEqualTo($viewName);
                    $this->array($parameters)
                        ->hasKeys(['log', 'base_layout']);
                    $this->object($parameters['log'])
                        ->isInstanceOf(Log::class);
                    $this->variable($parameters['base_layout'])
                        ->isNull;
                },

                $request = new MockOfRequest,
                $this->newTestedInstance(),
                $this->testedInstance->setContainer($container),
                $id = 1
            )
            ->if($result = $this->testedInstance->showAction($request, $id))
            ->then
                ->object($result)
                    ->isInstanceOf(Response::class);

        $this
            ->assert('Test if $log is null and client is authorized.')
            ->given(
                $logsModelRepository = new MockOfLogsModelRepository,
                $this->calling($logsModelRepository)->getLogById = new MockOfLog,
                $container = new MockOfContainer,
                $this->calling($container)->get = function($serviceName) use ($logsModelRepository) {
                    switch ($serviceName) {
                        case 'norsys_logs.model.log_repository':
                            return $logsModelRepository;
                        default: throw new \Exception(
                            'Service "%s" not found for get method.',
                            $serviceName
                        );
                    }
                },
                $this->calling($container)->getParameter = function($name) {
                    switch($name) {
                        case 'norsys_logs.security.enabled':
                            return false;
                        case 'norsys_logs.security.allowed_ips':
                            return ['128.0.0.1'];
                        default:
                            throw new \Exception('Parameter for container not handled in test.');
                    }
                },
                $this->calling($logsModelRepository)->getLogById = null,
                $this->newTestedInstance(),
                $this->testedInstance->setContainer($container),

                $request = new MockOfRequest,
                $id = 1
            )
            ->exception(
                function() use ($request, $id) {
                    $this->testedInstance->showAction($request, $id);
                }
            )->isInstanceOf(NotFoundHttpException::class)
            ->hasMessage('The log entry does not exist');

        $this
            ->assert('Test if client is not authorized.')
            ->given(
                $logsModelRepository = new MockOfLogsModelRepository,
                $this->calling($logsModelRepository)->getLogById = new MockOfLog,
                $container = new MockOfContainer,
                $this->calling($container)->get = function($serviceName) use ($logsModelRepository) {
                    switch ($serviceName) {
                        case 'norsys_logs.model.log_repository':
                            return $logsModelRepository;
                        default: throw new \Exception(
                            'Service "%s" not found for get method.',
                            $serviceName
                        );
                    }
                },
                $this->calling($logsModelRepository)->getLogById = null,
                $this->calling($container)->getParameter = function($name) {
                    switch($name) {
                        case 'norsys_logs.security.enabled':
                            return true;
                        case 'norsys_logs.security.allowed_ips':
                            return ['128.0.0.1'];
                        default:
                            throw new \Exception('Parameter for container not handled in test.');
                    }
                },
                $this->newTestedInstance(),
                $this->testedInstance->setContainer($container),

                $request = new MockOfRequest,
                $id = 1
            )
            ->exception(
                function() use ($request, $id) {
                    $this->testedInstance->showAction($request, $id);
                }
            )->isInstanceOf(NotFoundHttpException::class);
    }
}
