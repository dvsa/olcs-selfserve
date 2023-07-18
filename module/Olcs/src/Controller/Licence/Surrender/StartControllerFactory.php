<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Dispatcher;
use Common\Service\Helper\FlashMessengerHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class StartControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StartController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get('Helper\Translation');
        $formHelper = $container->get('Helper\Form');
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        return new StartController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessenger);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): StartController
    {
        return $this->__invoke($serviceLocator, StartController::class);
    }
}
