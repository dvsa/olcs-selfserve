<?php

namespace Olcs\Action\Licence\Vehicle;

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

class TransferVehicleConfirmationIndexActionFactory implements FactoryInterface
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

        $queryHandler = $controllerPluginManager->get('handleQuery');
        assert($queryHandler instanceof HandleQuery, 'Expected instance of HandleQuery');

        $formHelper = new FormHelperService();
        $formHelper->setServiceLocator($serviceLocator);

        $urlPlugin = $controllerPluginManager->get(Url::class);
        assert($urlPlugin instanceof Url, 'Expected instance of Url');

        $redirectPlugin = $controllerPluginManager->get(Redirect::class);
        assert($redirectPlugin instanceof Redirect, 'Expected instance of Redirect');

        return new TransferVehicleConfirmationIndexAction(
            (new FlashMessengerHelperService())->setServiceLocator($serviceLocator),
            $translationService,
            new LicenceVehicleManagement(),
            $formHelper,
            new LicenceRepository($queryHandler),
            new LicenceVehicleRepository($queryHandler),
            $urlPlugin,
            $redirectPlugin
        );
    }
}