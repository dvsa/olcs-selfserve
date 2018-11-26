<?php

namespace Permits\Data\Mapper;

use Common\Exception\BadRequestException;
use Common\Service\Helper\TranslationHelperService;
use Permits\Controller\Config\DataSource\LicencesAvailable as LicencesAvailableDataSource;


/**
 *
 * Change Licence mapper
 */
class ChangeLicence
{
    /**
     * @param array $data
     *
     * @return bool
     * @throws BadRequestException
     */
    public static function validateData(array $allData): bool
    {
        $data = $allData[LicencesAvailableDataSource::DATA_KEY];
        $data['licence'] = $allData['licence'];

        if (!$data['hasAvailableLicences']) {
            throw new BadRequestException('No available licences.');
        }

        $selectedLicenceEligible = array_search($data['licence'], array_column($data['eligibleLicences']['result'], 'id'));

        if ($selectedLicenceEligible === false) {
            throw new BadRequestException('User does not own selected licence.');
        } else {
            if ($data['eligibleLicences']['result'][$selectedLicenceEligible]['hasActiveApplication']) {
                throw new BadRequestException('Selected licence already has an active application.');
            }
        }

        return true;
    }

    public static function mapForFormOptions(array $data, $form, TranslationHelperService $translator)
    {
        if (self::validateData($data)) {
            $mapData = $data[LicencesAvailableDataSource::DATA_KEY];

            $selectedLicenceEligible = array_search(
                $data['licence'],
                array_column($mapData['eligibleLicences']['result'], 'id')
            );

            $confirmChangeLabel = $translator->translateReplace(
                'permits.form.change_licence.label',
                [$mapData['eligibleLicences']['result'][$selectedLicenceEligible]['licNo']]
            );

            var_dump($confirmChangeLabel);

            $form->get('fields')->get('ConfirmChange')->setLabel(
                $confirmChangeLabel
            );

            return $data;
        }
    }
}
