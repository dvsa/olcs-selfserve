<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Controller\Dispatcher;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class EssentialInformationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EssentialInformationController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get('Helper\Translation');
        $formHelper = $container->get('Helper\Form');
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        return new EssentialInformationController($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return EssentialInformationController
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): EssentialInformationController
    {
        return $this->__invoke($serviceLocator, EssentialInformationController::class);
    }
}
