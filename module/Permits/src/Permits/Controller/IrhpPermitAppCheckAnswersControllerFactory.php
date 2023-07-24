<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class IrhpPermitAppCheckAnswersControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpPermitAppCheckAnswersController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpPermitAppCheckAnswersController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get('Table');
        $mapperManager = $container->get(MapperManager::class);
        $languagePreference = $container->get('LanguagePreference');
        return new IrhpPermitAppCheckAnswersController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $languagePreference);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return IrhpPermitAppCheckAnswersController
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IrhpPermitAppCheckAnswersController
    {
        return $this->__invoke($serviceLocator, IrhpPermitAppCheckAnswersController::class);
    }
}
