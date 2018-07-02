<?php

namespace Norsys\LogsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function indexAction(Request $request)
    {
        $query = $this->getLogRepository()->getLogsQueryBuilder();

        if ($this->clientHasAccess($request) === false) {
            throw $this->createNotFoundException();
        }

        $pagination = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->get('page', 1),
            $this->container->getParameter('norsys_logs.logs_per_page')
        );

        return $this->render('NorsysLogsBundle:Default:index.html.twig', array(
            'pagination'  => $pagination,
            'base_layout' => $this->getBaseLayout(),
        ));
    }

    /**
     * @param Request $request
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction(Request $request, int $id)
    {
        $log = $this->getLogRepository()->getLogById($id);

        if ($this->clientHasAccess($request) === false) {
            throw $this->createNotFoundException();
        }

        if (null === $log) {
            throw $this->createNotFoundException('The log entry does not exist');
        }

        return $this->render('NorsysLogsBundle:Default:show.html.twig', array(
            'log'          => $log,
            'base_layout'  => $this->getBaseLayout(),
        ));
    }

    /**
     * @return string
     */
    protected function getBaseLayout()
    {
        return $this->container->getParameter('norsys_logs.base_layout');
    }

    /**
     * @return \Norsys\LogsBundle\Model\LogRepository
     */
    protected function getLogRepository()
    {
        return $this->get('norsys_logs.model.log_repository');
    }

    /**
     * @param Request $request
     *
     * @return boolean
     */
    protected function clientHasAccess(Request $request)
    {
        $securityEnabled    = $this->getParameter('norsys_logs.security.enabled');
        $securityAllowedIps = $this->getParameter('norsys_logs.security.allowed_ips');

        if ($securityEnabled === true
            && in_array($request->getClientIp(), $securityAllowedIps) === false
        ) {
            return false;
        }

        return true;
    }
}
