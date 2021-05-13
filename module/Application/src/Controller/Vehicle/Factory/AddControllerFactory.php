<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicle\Factory;

use Common\Controller\Dispatcher;
use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\FeatureToggle;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Application\Controller\Vehicle\AddController;
use Dvsa\Olcs\Application\Controller\VehiclesController;
use Dvsa\Olcs\Application\Session\Vehicles;
use Interop\Container\ContainerInterface;
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
            $container->get(FormHelperService::class),
            $controllerPluginManager->get(HandleQuery::class),
            $container->get(Vehicles::class),
            $urlHelper = $controllerPluginManager->get(Url::class)
        );

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $urlHelper->setController($instance);

        return $instance;
    }

    /**
     * @inheritDoc
     */
    protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Throw a real error here
        $instance = new VehiclesController();
        if ($instance instanceof FactoryInterface) {
            $instance = $instance->createService($container);
        }

        var_dump($instance);
        return $instance;
    }
}
