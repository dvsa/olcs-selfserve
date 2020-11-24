<?php

namespace Olcs\Controller;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\ControllerManager AS ZendControllerManager;

class ControllerManger extends ZendControllerManager
{
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
            $instance = new ActionDispatchDecorator($instance);
            $delegate = $instance->getDelegate();
            if ($delegate instanceof DelegatesPluginsInterface) {
                foreach ($delegate->getDelegatedPlugins() as $plugin) {
                    if ($plugin instanceof AbstractPlugin) {
                        $plugin->setController($instance);
                    }
                }
            }
        }
        return $instance;
    }
}
