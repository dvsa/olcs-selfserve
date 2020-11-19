<?php

namespace Olcs\DTO\Licence\OtherActiveLicenceList;

use Olcs\DTO\DataTransferObject;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\DTO\Licence\OtherActiveLicenceList\OtherActiveLicenceListLicenceDTO;
use Olcs\DTO\Licence\OtherActiveLicenceList\OtherActiveLicenceListOtherLicenceDTO;

class OtherActiveLicenceListDTO extends DataTransferObject
{
    /**
     * Gets the licence which the other licence list is in relation to.
     *
     * @return LicenceDTO
     */
    public function getLicence()
    {
        return new LicenceDTO($this->data);
    }

    /**
     * Gets the other active licences.
     *
     * @return LicenceDTO[]
     */
    public function getOtherLicences()
    {
        $otherActiveLicenceData = $this->data['otherActiveLicences'] ?? null;
        return array_map(function ($licenceData) {
            return new LicenceDTO($licenceData);
        }, is_array($otherActiveLicenceData) ? $otherActiveLicenceData : []);
    }
}
