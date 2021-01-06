<?php

namespace Olcs\Controller;

class ControllerManager extends \Laminas\Mvc\Controller\ControllerManager
{
    /**
     * @var array
     */
    protected $delegateActionDependencyResolverMappings;

    /**
     * @param $configOrContainerInstance
     * @param array $v3config
     */
    public function __construct($configOrContainerInstance, array $v3config = [])
    {
        parent::__construct($configOrContainerInstance, $v3config);

        $delegateActionDependencyResolverMappings = $v3config['delegate_action_dependency_resolvers'] ?? [];
        if (is_array($delegateActionDependencyResolverMappings)) {
            $this->delegateActionDependencyResolverMappings = $delegateActionDependencyResolverMappings;
        }
    }

    /**
     * @inheritDoc
     */
    public function createFromFactory($canonicalName, $requestedName)
    {
        $instance = parent::createFromFactory($canonicalName, $requestedName);
        $instance = $this->initializeDispatchDelegatingControllers($instance);
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function createFromInvokable($canonicalName, $requestedName)
    {
        $instance = parent::createFromInvokable($canonicalName, $requestedName);
        $instance = $this->initializeDispatchDelegatingControllers($instance);
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function createFromAbstractFactory($canonicalName, $requestedName)
    {
        $instance = parent::createFromAbstractFactory($canonicalName, $requestedName);
        $instance = $this->initializeDispatchDelegatingControllers($instance);
        return $instance;
    }

    /**
     * Initialises any dispatch delegating controller instances.
     *
     * @param mixed $instance
     * @return mixed
     */
    public function initializeDispatchDelegatingControllers($instance)
    {
        if ($instance instanceof DelegatesDispatchingInterface) {
            $instance = new ActionDispatchDecorator($instance, $this->delegateActionDependencyResolverMappings);
        }
        return $instance;
    }
}
