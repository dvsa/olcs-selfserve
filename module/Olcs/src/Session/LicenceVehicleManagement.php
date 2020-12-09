<?php


namespace Olcs\Session;

use Olcs\Exception\Licence\Vehicle\VehicleSelectionEmptyException;

class LicenceVehicleManagement extends \Laminas\Session\Container
{
    const SESSION_NAME = 'LicenceVehicleManagement';
    protected const TRANSFER_TO_LICENSE_ID = 'transferToLicenceId';
    protected const KEY_CONFIRMATION_FIELD_MESSAGES = 'confirmationFieldMessages';

    /**
     * LicenceVehicleManagement constructor.
     */
    public function __construct()
    {
        parent::__construct(self::SESSION_NAME);
    }

    /**
     * @return bool
     */
    public function hasVrm(): bool
    {
        return $this->offsetExists('vrm');
    }

    /**
     * @return string
     */
    public function getVrm()
    {
        return $this->offsetGet('vrm');
    }

    /**
     * @param string $vrm
     * @return $this
     */
    public function setVrm(string $vrm): LicenceVehicleManagement
    {
        $this->offsetSet('vrm', $vrm);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasVrms(): bool
    {
        return $this->offsetExists('vrms');
    }

    /**
     * @return array
     */
    public function getVrms()
    {
        $vrms = $this->offsetGet('vrms');
        return array_map(function ($vrm) {
            return (int) $vrm;
        }, is_array($vrms) ? $vrms : []);
    }

    /**
     * @param array $vrm
     * @return $this
     */
    public function setVrms(array $vrm): LicenceVehicleManagement
    {
        $this->offsetSet('vrms', $vrm);
        return $this;
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
    public function setVehicleData(array $vehicleData): LicenceVehicleManagement
    {
        $this->offsetSet('vehicleData', $vehicleData);
        return $this;
    }

    /**
     * Determines whether a session has a licence id set for which a related set of vehicles should be transferred to.
     *
     * @return bool
     */
    public function hasDestinationLicenceId(): bool
    {
        return $this->offsetExists(static::TRANSFER_TO_LICENSE_ID);
    }

    /**
     * Gets a licence id for which a related set of vehicles should be transferred to.
     *
     * @return int|null
     */
    public function getDestinationLicenceId(): ?int
    {
        return $this->offsetGet(static::TRANSFER_TO_LICENSE_ID) ?: null;
    }

    /**
     * Sets a licence id for which a related set of vehicles should be transferred to.
     *
     * @param int $id
     * @return $this
     */
    public function setDestinationLicenceId(int $id)
    {
        $this->offsetSet(static::TRANSFER_TO_LICENSE_ID, $id);
        return $this;
    }

    /**
     * Adds a message for the confirmation form field.
     *
     * @param array<string> $messages
     * @return $this
     */
    public function setConfirmationFieldMessages(array $messages)
    {
        $this->offsetSet(static::KEY_CONFIRMATION_FIELD_MESSAGES, $messages);
        return $this;
    }

    /**
     * Gets any messages for the confirmation form field and removes them from the session.
     *
     * @return array<string>
     */
    public function pullConfirmationFieldMessages()
    {
        $messages = $this->offsetGet(static::KEY_CONFIRMATION_FIELD_MESSAGES);
        $this->offsetUnset(static::KEY_CONFIRMATION_FIELD_MESSAGES);
        return $messages;
    }

    /**
     * Destroys session container
     */
    public function destroy(): void
    {
        $this->getManager()->getStorage()->clear(static::SESSION_NAME);
    }
}
