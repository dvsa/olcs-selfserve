<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Application\Controller\BusinessDetailsController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use LmcRbacMvc\Service\AuthorizationService;

class BusinessDetailsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BusinessDetailsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusinessDetailsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new BusinessDetailsController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $identityProvider,
            $restrictionHelper,
            $stringHelper,
            $tableFactory,
            $uploadHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BusinessDetailsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): BusinessDetailsController
    {
        return $this->__invoke($serviceLocator, BusinessDetailsController::class);
    }
}
