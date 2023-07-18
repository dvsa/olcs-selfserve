<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Dispatcher;
use Common\Data\Mapper\DefaultMapper;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Controller\PermitsController;
use Permits\Data\Mapper\MapperManager;

class SignatureVerificationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SignatureVerificationController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get('Helper\Translation');
        $formHelper = $container->get('Helper\Form');
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        return new SignatureVerificationController($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): SignatureVerificationController
    {
        return $this->__invoke($serviceLocator, SignatureVerificationController::class);
    }
}
