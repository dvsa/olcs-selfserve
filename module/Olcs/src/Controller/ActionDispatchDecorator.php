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
     * @var array
     */
    protected $delegateActionDependancyResolvers;

    /**
     * @param object $delegate
     * @param array $delegateActionDependancyResolvers
     */
    public function __construct(object $delegate, array $delegateActionDependancyResolvers)
    {
        $this->delegate = $delegate;
        $this->eventIdentifier = get_class($delegate);
        $this->delegateActionDependancyResolvers = $delegateActionDependancyResolvers;
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
        if (! method_exists($this->delegate, $action)) {
            throw new NotFoundHttpException();
        }
        
        var_dump('HERE');
        die();

        // @todo resolve argument resolver
        $actionArgs = []; // @todo resolve method arguments
        if (is_callable([$this->delegate, 'dispatch'])) {
            // @todo resolve dispatch method arguments
            $actionArgs = array_merge([$this->serviceLocator, $actionArgs], []);
            $action = 'dispatch';
        }
        return $this->delegate->$action(...$methodArgs);
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