<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Surrender;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Permits\Data\Mapper\MapperManager;

class InformationChangedControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InformationChangedController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get(TableFactory::class);
        $mapperManager = $container->get(MapperManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        return new InformationChangedController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }
}
