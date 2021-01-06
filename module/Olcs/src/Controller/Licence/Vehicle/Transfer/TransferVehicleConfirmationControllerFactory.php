<?php

namespace Olcs\Controller\Licence\Vehicle\Transfer;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Repository\Licence\LicenceRepository;
use Olcs\Repository\Licence\Vehicle\LicenceVehicleRepository;
use Olcs\Session\LicenceVehicleManagement;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;

/**
 * @see TransferVehicleConfirmationController
 */
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

        return new TransferVehicleConfirmationController(
            (new FlashMessengerHelperService())->setServiceLocator($serviceLocator),
            $translationService,
            new LicenceVehicleManagement(),
            $commandBus,
            $formHelper,
            new LicenceRepository($queryHandler),
            new LicenceVehicleRepository($queryHandler)
        );
    }
}