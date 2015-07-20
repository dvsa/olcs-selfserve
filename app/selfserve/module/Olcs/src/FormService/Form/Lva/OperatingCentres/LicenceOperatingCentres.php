<?php

/**
 * Licence Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres as CommonLicenceOperatingCentres;
use Zend\Form\Form;

/**
 * Licence Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentres extends CommonLicenceOperatingCentres
{
    protected $mainTableConfigName = 'lva-licence-operating-centres';

    private $lockElements = [
        'totAuthSmallVehicles',
        'totAuthMediumVehicles',
        'totAuthLargeVehicles',
        'totAuthVehicles',
        'totAuthTrailers',
        'totCommunityLicences'
    ];

    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        $this->getFormHelper()->disableElements($form->get('data'));

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }

        foreach ($this->lockElements as $lockElement) {
            if ($form->get('data')->has($lockElement)) {
                $this->getFormHelper()->lockElement(
                    $form->get('data')->get($lockElement),
                    'operating-centres-licence-locked'
                );
            }
        }
    }
}
