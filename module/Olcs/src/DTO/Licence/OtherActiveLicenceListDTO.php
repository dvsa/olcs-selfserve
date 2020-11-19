<?php

namespace Olcs\DTO\Licence;

use Olcs\DTO\DataTransferObject;

class OtherActiveLicenceListDTO extends DataTransferObject
{
    /**
     * Gets the licence which the other licence list is in relation to.
     *
     * @return OtherActiveLicenceListLicenceDTO
     */
    public function getLicence()
    {
        return new OtherActiveLicenceListLicenceDTO($this->data);
    }

    /**
     * Gets the other active licences.
     *
     * @return OtherActiveLicenceListOtherLicenceDTO[]
     */
    public function getOtherLicences()
    {
        $otherActiveLicenceData = $this->data['otherActiveLicences'] ?? null;
        return array_map(function ($licenceData) {
            return new OtherActiveLicenceListOtherLicenceDTO($licenceData);
        }, is_array($otherActiveLicenceData) ? $otherActiveLicenceData : []);
    }
}
