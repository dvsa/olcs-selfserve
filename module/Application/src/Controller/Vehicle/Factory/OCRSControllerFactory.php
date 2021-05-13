<?php
declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicle\Factory;

use Common\Controller\Dispatcher;
use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\FeatureToggle;
use Dvsa\Olcs\Application\Controller\Vehicle\OCRSController;
use Interop\Container\ContainerInterface;

class OCRSControllerFactory extends BinaryFeatureToggleAwareControllerFactory
{
    /**
     * @inheritDoc
     */
    protected function getFeatureToggleNames(): array
    {
        return [FeatureToggle::DVLA_INTEGRATION];
    }

    /**
     * @inheritDoc
     */
    protected function createServiceWhenEnabled(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Dispatcher(new OCRSController());
    }

    /**
     * @inheritDoc
     */
    protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Throw a real error here
        die("Not Implemented for LVA journey");
    }
}
