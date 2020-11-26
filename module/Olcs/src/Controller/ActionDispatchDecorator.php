<?php

namespace Olcs\Controller;

use Exception;
use Olcs\Exception\Http\NotFoundHttpException;

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

        try {
            if (! method_exists($this->delegate, $method)) {
                throw new NotFoundHttpException();
            }
            $actionResponse = $this->delegate->$method($routeMatch, $request);
        } catch (NotFoundHttpException $exception) {
            return $this->notFoundAction();
        } catch (Exception $exception) {
            if (! ($this->delegate instanceof RespondsToExceptionsInterface)) {
                throw $exception;
            }
            $actionResponse = $this->delegate->createResponseToException($exception, $routeMatch, $request);
        }
        return $actionResponse;
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