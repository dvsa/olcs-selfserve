<?php

namespace Olcs\Service\Cookie;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DeleteCookieNamesProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DeleteCookieNamesProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $deleteCookieNamesProvider = new DeleteCookieNamesProvider();

        $deleteCookieNamesProvider->registerCookieNamesProvider(
            Preferences::KEY_ANALYTICS,
            $serviceLocator->get('CookieAnalyticsCookieNamesProvider')
        );

        $deleteCookieNamesProvider->registerCookieNamesProvider(
            Preferences::KEY_SETTINGS,
            $serviceLocator->get('CookieSettingsCookieNamesProvider')
        );

        return $deleteCookieNamesProvider;
    }
}
