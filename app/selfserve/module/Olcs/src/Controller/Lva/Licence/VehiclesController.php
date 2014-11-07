<?php

/**
 * External Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\AbstractGenericVehiclesGoodsController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * External Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesGoodsController
{
    use LicenceControllerTrait,
        Traits\LicenceGenericVehiclesControllerTrait,
        Traits\LicenceGoodsVehiclesControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * This method is used to hook the trait's pre & post save methods into the parent save vehicle method
     *
     * @param array $data
     * @param string $mode
     */
    protected function saveVehicle($data, $mode)
    {
        $data = $this->preSaveVehicle($data, $mode);

        $licenceVehicleId = parent::saveVehicle($data, $mode);

        $this->postSaveVehicle($licenceVehicleId, $mode);
    }
}
