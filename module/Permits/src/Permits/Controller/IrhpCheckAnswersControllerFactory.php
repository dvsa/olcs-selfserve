<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Controller\Dispatcher;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IrhpCheckAnswersControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpCheckAnswersController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpCheckAnswersController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        $languagePreference = $container->get('LanguagePreference');
        return new IrhpCheckAnswersController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $languagePreference);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return IrhpCheckAnswersController
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IrhpCheckAnswersController
    {
        return $this->__invoke($serviceLocator, IrhpCheckAnswersController::class);
    }
}
