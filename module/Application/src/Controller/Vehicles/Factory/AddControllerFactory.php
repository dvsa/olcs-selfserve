<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicles\Factory;

use Common\Controller\Dispatcher;
use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\FeatureToggle;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Application\Controller\Vehicles\AddController;
use Dvsa\Olcs\Application\Controller\VehiclesController;
use Dvsa\Olcs\Application\Session\Vehicles;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;

/**
 * @See AddController
 */
class AddControllerFactory extends BinaryFeatureToggleAwareControllerFactory
{

    /**
     * @inheritDoc
     */
    protected function getFeatureToggleNames(): array
    {
        return [FeatureToggle::DVLA_INTEGRATION];
    }

    /**
     * @inheritDoc
     */
    protected function createServiceWhenEnabled(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($container instanceof ServiceLocatorAwareInterface) {
            $container = $container->getServiceLocator();
        }
        $controllerPluginManager = $container->get('ControllerPluginManager');

        $controller = new AddController(
            $controllerPluginManager->get(HandleCommand::class),
            $controllerPluginManager->get(FlashMessenger::class),
            $container->get(FormHelperService::class),
            $controllerPluginManager->get(HandleQuery::class),
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
            $container->get(Vehicles::class),
            $container->get(TranslationHelperService::class),
            $urlHelper = $controllerPluginManager->get(Url::class)
        );

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $urlHelper->setController($instance);
        $redirectHelper->setController($instance);

        return $instance;
    }

    /**
     * @inheritDoc
     */
    protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null)
    {
        //TODO: Throw a real error here
        die("Not implemented for LVA journey");
    }
}
