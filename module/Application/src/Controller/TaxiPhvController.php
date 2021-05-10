<?php

/**
 * External Application Taxi PHV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Taxi PHV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaxiPhvController extends Lva\AbstractTaxiPhvController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
