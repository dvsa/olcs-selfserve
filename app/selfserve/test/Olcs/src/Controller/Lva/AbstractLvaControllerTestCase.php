<?php

namespace OlcsTest\Controller\Lva;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Lva\Traits\LvaControllerTestTrait;

/**
 * Helper functions for testing LVA controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractLvaControllerTestCase extends MockeryTestCase
{
    use LvaControllerTestTrait;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->request = m::mock('\Zend\Http\Request')->makePartial();
    }
}
