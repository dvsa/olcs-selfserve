<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Dispatcher;
use Common\Service\Helper\FlashMessengerHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class OperatorLicenceControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorLicenceController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get('Helper\Translation');
        $formHelper = $container->get('Helper\Form');
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        return new OperatorLicenceController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): OperatorLicenceController
    {
        return $this->__invoke($serviceLocator, OperatorLicenceController::class);
    }
}
