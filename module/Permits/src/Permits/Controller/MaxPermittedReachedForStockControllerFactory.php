<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Controller\Dispatcher;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MaxPermittedReachedForStockControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MaxPermittedReachedForStockController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MaxPermittedReachedForStockController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get('Helper\Translation');
        $formHelper = $container->get('Helper\Form');
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        return new MaxPermittedReachedForStockController($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return MaxPermittedReachedForStockController
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): MaxPermittedReachedForStockController
    {
        return $this->__invoke($serviceLocator, MaxPermittedReachedForStockController::class);
    }
}
