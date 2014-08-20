<?php

/**
 * TaxiPhv Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\TaxiPhv;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * TaxiPhv Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaxiPhvControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\SelfServe\Controller\Application\TaxiPhv\TaxiPhvController';

    protected $defaultRestResponse = array();

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    protected function mockRestCalls($service, $method, $data = array(), $bundle = array())
    {
        if ($service == 'Application' && $method == 'GET' && $bundle == ApplicationController::$licenceDataBundle) {

            return $this->getLicenceData('goods');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }
    }
}
