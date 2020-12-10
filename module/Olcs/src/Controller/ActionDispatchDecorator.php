<?php

namespace Olcs\Controller;

use Exception;
use Olcs\Exception\Http\NotFoundHttpException;
use Laminas\Mvc\MvcEvent;

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
        $this->eventIdentifier = get_class($delegate);
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
    public function onDispatch(MvcEvent $e)
    {
        try {
            return parent::onDispatch($e);
        } catch (NotFoundHttpException $exception) {
            return $this->notFoundAction();
        }
    }

    /**
     * Calls an action.
     *
     * @return mixed
     * @throws Exception
     */
    protected function callAction()
    {
        $request = $this->getEvent()->getRequest();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $action = $routeMatch->getParam('action');
        $method = is_null($action) ? '__invoke' : parent::getMethodFromAction($action);
        if (! method_exists($this->delegate, $method)) {
            throw new NotFoundHttpException();
        }
        return $this->delegate->$method($routeMatch, $request);
    }

    /**
     * @inheritDoc
     */
    public static function getMethodFromAction($action)
    {
        // Delegate all action calls to $this->callAction
        return 'callAction';
    }
}