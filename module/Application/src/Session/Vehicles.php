<?php


namespace Dvsa\Olcs\Application\Session;

use Laminas\Session\Container;

class Vehicles extends Container
{
    const SESSION_NAME = 'ApplicationVehicles';

    public function __construct()
    {
        parent::__construct(self::SESSION_NAME);
    }

    /**
     * @return bool
     */
    public function hasVehicleData(): bool
    {
        return $this->offsetExists('vehicleData');
    }

    /**
     * @return array|null
     */
    public function getVehicleData()
    {
        return $this->offsetGet('vehicleData');
    }

    /**
     * @param array $vehicleData
     * @return $this
     */
    public function setVehicleData(array $vehicleData): Vehicles
    {
        $this->offsetSet('vehicleData', $vehicleData);
        return $this;
    }

    /**
     * Destroys session container
     */
    public function destroy(): void
    {
        $this->getManager()->getStorage()->clear(static::SESSION_NAME);
    }
}
