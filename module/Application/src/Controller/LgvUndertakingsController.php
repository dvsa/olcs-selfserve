<?php

namespace Dvsa\Olcs\Application\Controller;

use Olcs\Controller\Lva\AbstractLgvUndertakingsController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External application LGV undertakings controller
 */
class LgvUndertakingsController extends AbstractLgvUndertakingsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
}
