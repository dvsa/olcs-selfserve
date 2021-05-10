<?php

/**
 * External Application Convictions and penalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Convictions and penalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConvictionsPenaltiesController extends Lva\AbstractConvictionsPenaltiesController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
