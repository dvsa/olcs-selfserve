<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Factory\Adapter;

use Common\Service\Lva\PeopleLvaService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;

class ApplicationPeopleAdapterFactory implements FactoryInterface
{
    /**
     * @deprecated Laminas 2 compatibility. To be removed after Laminas 3 upgrade.
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ApplicationPeopleAdapter
    {
        $container = method_exists($serviceLocator, 'getServiceLocator') ? $serviceLocator->getServiceLocator() : $serviceLocator;

        return $this->__invoke($container, LicencePeopleAdapter::class);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationPeopleAdapter
    {
        $peopleLvaService = $container->get(PeopleLvaService::class);
        return new ApplicationPeopleAdapter($container, $peopleLvaService);
    }
}
