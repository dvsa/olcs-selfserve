<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Dispatcher;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Script\ScriptFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class CurrentDiscsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CurrentDiscsController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get('Helper\Translation');
        $formHelper = $container->get('Helper\Form');
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        return new CurrentDiscsController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper, $scriptFactory);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return CurrentDiscsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): CurrentDiscsController
    {
        return $this->__invoke($serviceLocator, CurrentDiscsController::class);
    }
}
