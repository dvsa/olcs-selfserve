<?php

declare(strict_types=1);

namespace Olcs\Mvc\Strategy\Validation;

use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @see ValidationStrategy
 */
class ValidationStrategyFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ValidationStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ValidationStrategy
    {
        return new ValidationStrategy(
            $serviceLocator->get(TranslatorInterface::class),
            $serviceLocator->get('ControllerPluginManager')->get('FlashMessenger')
        );
    }
}
