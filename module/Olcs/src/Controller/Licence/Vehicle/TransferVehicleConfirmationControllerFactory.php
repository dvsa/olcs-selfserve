<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Repository\Licence\LicenceRepository;
use Olcs\Repository\Licence\Vehicle\LicenceVehicleRepository;
use Olcs\Session\LicenceVehicleManagement;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;
use Zend\Mvc\Controller\Plugin\Redirect;

class TransferVehicleConfirmationControllerFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        assert($serviceLocator instanceof ControllerManager, 'Expected instance of ControllerManager');
        $serviceLocator = $serviceLocator->getServiceLocator();

        $controllerPluginManager = $serviceLocator->get('ControllerPluginManager');
        assert($controllerPluginManager instanceof ControllerPluginManager);

        $translationService = new TranslationHelperService();
        $translationService->setServiceLocator($serviceLocator);

        $commandBus = $controllerPluginManager->get('handleCommand');
        assert($commandBus instanceof HandleCommand, 'Expected instance of HandleCommand');

        $queryHandler = $controllerPluginManager->get('handleQuery');
        assert($queryHandler instanceof HandleQuery, 'Expected instance of HandleQuery');

        $formHelper = new FormHelperService();
        $formHelper->setServiceLocator($serviceLocator);

        $urlPlugin = $controllerPluginManager->get(Url::class);
        assert($urlPlugin instanceof Url, 'Expected instance of Url');

        $redirectPlugin = $controllerPluginManager->get(Redirect::class);
        assert($redirectPlugin instanceof Redirect, 'Expected instance of Redirect');

        $controller = new TransferVehicleConfirmationController(
            (new FlashMessengerHelperService())->setServiceLocator($serviceLocator),
            $translationService,
            new LicenceVehicleManagement(),
            $commandBus,
            $formHelper,

            // @todo implement factories for these 2
            new LicenceRepository($queryHandler),
            new LicenceVehicleRepository($queryHandler),

            $urlPlugin,
            $redirectPlugin
        );

        $urlPlugin->setController($controller);
        $redirectPlugin->setController($controller);

        return $controller;
    }
}