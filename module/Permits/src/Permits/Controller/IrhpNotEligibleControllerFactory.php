<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class IrhpNotEligibleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpNotEligibleController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpNotEligibleController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        return new IrhpNotEligibleController($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return IrhpNotEligibleController
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IrhpNotEligibleController
    {
        return $this->__invoke($serviceLocator, IrhpNotEligibleController::class);
    }
}
