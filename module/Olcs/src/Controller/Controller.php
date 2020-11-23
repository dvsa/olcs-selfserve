<?php


namespace Olcs\Controller;

use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Exception\DomainException;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ConsoleModel;
use Zend\View\Model\ViewModel;

class Controller extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    protected $eventIdentifier = __CLASS__;

    /**
     * Action called if matched action does not exist
     *
     * @return ViewModel|ConsoleModel
     */
    public function notFoundAction()
    {
        $response   = $this->response;
        $event      = $this->getEvent();
        $routeMatch = $event->getRouteMatch();
        $routeMatch->setParam('action', 'not-found');

        if ($response instanceof HttpResponse) {
            return $this->createHttpNotFoundModel($response);
        }
        return $this->createConsoleNotFoundModel($response);
    }

    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     *
     * @throws Exception\DomainException If no RouteMatch was found within MvcEvent.
     */
    public function onDispatch(MvcEvent $e)
    {
        // @todo use toggle aware as trait from AbtractOlcsController

        // @todo extract dispatcher

        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            /**
             * @todo Determine requirements for when route match is missing.
             *       Potentially allow pulling directly from request metadata?
             */
            throw new DomainException('Missing route matches; unsure how to retrieve action');
        }

        $action = $routeMatch->getParam('action', 'not-found');
        $method = static::getMethodFromAction($action);

        if (!method_exists($this, $method)) {
            $method = 'notFoundAction';
        }

        $actionResponse = $this->$method($routeMatch, $e->getRequest());

        $e->setResult($actionResponse);

        return $actionResponse;
    }

    /**
     * @deprecated please use the {@see \Zend\Mvc\Controller\Plugin\CreateHttpNotFoundModel} plugin instead: this
     *             method will be removed in release 2.5 or later.
     *
     * {@inheritDoc}
     */
    protected function createHttpNotFoundModel(HttpResponse $response)
    {
        return $this->__call('createHttpNotFoundModel', [$response]);
    }

    /**
     * @deprecated please use the {@see \Zend\Mvc\Controller\Plugin\CreateConsoleNotFoundModel} plugin instead: this
     *             method will be removed in release 2.5 or later.
     *
     * {@inheritDoc}
     */
    protected function createConsoleNotFoundModel($response)
    {
        return $this->__call('createConsoleNotFoundModel', [$response]);
    }
}