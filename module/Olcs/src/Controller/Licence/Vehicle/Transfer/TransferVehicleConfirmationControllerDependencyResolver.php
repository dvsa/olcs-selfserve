<?php

namespace Olcs\Controller\Licence\Vehicle\Transfer;

use Laminas\Http\Request;
use Laminas\Mvc\Router\RouteMatch;
use Olcs\Controller\DelegateActionDependencyResolver;

/**
 * @see TransferVehicleConfirmationController
 */
class TransferVehicleConfirmationControllerDependencyResolver extends DelegateActionDependencyResolver
{
    /**
     * @inheritDoc
     */
    public function resolveDispatchArguments(string $action, array $arguments, RouteMatch $routeMatch, Request $request): array
    {
        return [$action, $arguments, $routeMatch, $this->resolveRedirectPlugin()];
    }

    /**
     * Invokes the "indexAction" method.
     *
     * @param RouteMatch $routeMatch
     * @param Request $request
     * @return array
     */
    public function resolveIndexActionArguments(RouteMatch $routeMatch, Request $request): array
    {
        return [$routeMatch, $request, $this->resolveUrlPlugin()];
    }

    /**
     * Invokes the "storeAction" method.
     *
     * @param RouteMatch $routeMatch
     * @param Request $request
     * @return array
     */
    public function resolveStoreActionArguments(RouteMatch $routeMatch, Request $request): array
    {
        return [$routeMatch, $request, $this->resolveRedirectPlugin()];
    }
}
