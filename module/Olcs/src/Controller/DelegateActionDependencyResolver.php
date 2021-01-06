<?php

namespace Olcs\Controller;

use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\DispatchableInterface;

abstract class DelegateActionDependencyResolver
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var ActionDispatchDecorator
     */
    protected $actionDecorator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param ActionDispatchDecorator $decorator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, ActionDispatchDecorator $decorator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->actionDecorator = $decorator;
    }

    /**
     * Resolves the "dispatch" method arguments.
     *
     * @param string $action
     * @param array $arguments
     * @param RouteMatch $routeMatch
     * @param Request $request
     * @return array
     */
    public function resolveDispatchArguments(string $action, array $arguments, RouteMatch $routeMatch, Request $request): array
    {
        return [$action, $arguments, $routeMatch, $request];
    }

    /**
     * Resolves any dependencies for an action.
     *
     * @param string $action
     * @param RouteMatch $routeMatch
     * @param Request $request
     * @return array
     */
    public function resolveActionArguments(string $action, RouteMatch $routeMatch, Request $request): array
    {
        $resolver = sprintf('resolve%sArguments', ucfirst($action));
        if (! is_callable([$this, $resolver])) {
            return [];
        }
        return $this->$resolver($routeMatch, $request);
    }

    /**
     * @return Redirect
     */
    protected function resolveRedirectPlugin(): Redirect
    {
        return $this->resolvePlugin($this->serviceLocator, $this->actionDecorator, Redirect::class);
    }

    /**
     * @return Url
     */
    protected function resolveUrlPlugin(): Url
    {
        return $this->resolvePlugin($this->serviceLocator, $this->actionDecorator, Url::class);
    }

    /**
     * @todo extract this to its own trait (will be useful for factories)
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param DispatchableInterface $dispathable
     * @param string $pluginKey
     * @return AbstractPlugin|DispatchableInterface|mixed
     */
    protected function resolvePlugin(ServiceLocatorInterface $serviceLocator, DispatchableInterface $dispathable, string $pluginKey)
    {
        $controllerPluginManager = $serviceLocator->get('ControllerPluginManager');
        assert($controllerPluginManager instanceof ControllerPluginManager);

        $plugin = $controllerPluginManager->get($pluginKey);;

        if ($plugin instanceof AbstractPlugin) {
            $plugin->setController($dispathable);
        }

        return $plugin;
    }
}