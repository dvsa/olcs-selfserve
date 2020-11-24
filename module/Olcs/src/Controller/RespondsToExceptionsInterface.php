<?php

namespace Olcs\Controller;

use Zend\Http\Response;
use Exception;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\RequestInterface;

interface RespondsToExceptionsInterface
{
    /**
     * Creates a response to a given exception.
     *
     * @param Exception $exception
     * @param RouteMatch $routeMatch
     * @param RequestInterface $request
     * @return Response
     * @throws Exception
     */
    public function createResponseToException(Exception $exception, RouteMatch $routeMatch, RequestInterface $request): Response;
}
