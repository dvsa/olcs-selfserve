<?php


namespace Olcs\DTO\Licence;

use Olcs\DTO\DataTransferObject;

class OtherActiveLicenceListLicenceDTO extends DataTransferObject
{
    protected const ATTRIBUTE_ID = 'id';
    protected const ATTRIBUTE_LICENCE_NUMBER = 'licNo';

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
     * @return string|null
     */
    public function getLicenceNumber(): ?string
    {
        return $this->data[static::ATTRIBUTE_LICENCE_NUMBER];
    }
}
