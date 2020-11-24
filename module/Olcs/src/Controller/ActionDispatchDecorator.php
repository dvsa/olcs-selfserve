<?php

namespace Olcs\Controller;

use Zend\Stdlib\RequestInterface;
use Zend\Mvc\Router\Http\RouteMatch;

class ActionDispatchDecorator extends AbstractSelfserveController
{
    /**
     * @var object
     */
    protected $delegate;

    /**
     * @param object $delegate
     */
    public function __construct(object $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * @return object
     */
    public function getDelegate(): object
    {
        return $this->delegate;
    }

    /**
     * @inheritDoc
     */
    protected function callAction(string $actionName, RouteMatch $routeMatch, RequestInterface $request)
    {
        try {
            $actionResponse = call_user_func([$this->delegate, $actionName], $routeMatch, $request);
        } catch (\Exception $exception) {
            if (! ($this->delegate instanceof RespondsToExceptionsInterface)) {
                throw $exception;
            }
            $actionResponse = $this->delegate->createResponseToException($exception, $routeMatch, $request);
        }
        return $actionResponse;
    }
}