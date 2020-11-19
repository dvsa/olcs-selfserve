<?php

namespace Olcs\DTO\Licence;

use Olcs\DTO\DataTransferObject;

class LicenceDTO extends DataTransferObject
{
    protected const ATTRIBUTE_ID = 'id';
    protected const ATTRIBUTE_LICENCE_NUMBER = 'licNo';
    protected const ATTRIBUTE_ACTIVE_VEHICLE_COUNT = 'activeVehicleCount';

    /**
     * Gets the id of a licence.
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->data[static::ATTRIBUTE_ID];
    }

    /**
     * Gets the licence number for a licence.
     *
     * @return string
     */
    public function getLicenceNumber(): string
    {
        return (string) $this->data[static::ATTRIBUTE_LICENCE_NUMBER];
    }

    /**
     * Gets the number of active vehicles that a licence is associated with.
     *
     * @return int|null
     */
    public function getActiveVehicleCount(): ?int
    {
        $count = $this->data[static::ATTRIBUTE_ACTIVE_VEHICLE_COUNT] ?? null;
        return null === $count ? $count : (int) $count;
    }
}
