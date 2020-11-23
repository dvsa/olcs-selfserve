<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Session\LicenceVehicleManagement;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;

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

        $queryBus = $controllerPluginManager->get('handleQuery');
        assert($queryBus instanceof HandleQuery, 'Expected instance of HandleQuery');

        $formHelper = new FormHelperService();
        $formHelper->setServiceLocator($serviceLocator);

        return new TransferVehicleConfirmationController(
            (new FlashMessengerHelperService())->setServiceLocator($serviceLocator),
            $translationService,
            new LicenceVehicleManagement(),
            $commandBus,
            $queryBus,
            $formHelper
        );
    }
}