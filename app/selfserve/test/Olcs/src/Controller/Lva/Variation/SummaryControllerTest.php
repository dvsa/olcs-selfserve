<?php

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\TransportManagers as Qry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryControllerTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock('\Olcs\Controller\Lva\Variation\SummaryController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);

        $this->sut->shouldReceive('params->fromRoute');
    }

    /**
     * @dataProvider indexActionProvider
     */
    public function testIndexAction($licenceCategory, $licenceType, $tmResults, $expectedWarningText, $expectedActions)
    {
        // Data
        $id = 3;

        $data = [
            'id' => $id,
            'licence' => [
                'id' => 4,
                'licNo' => 123456,
            ],
            'licenceType' => [
                'id' => $licenceType,
            ],
            'goodsOrPsv' => [
                'id' => $licenceCategory,
            ],
            'transportManagers' => $tmResults,
        ];

        // Mocks
        $response = m::mock();

        // Expectations
        $this->sut->shouldReceive('getIdentifier')
            ->andReturn($id);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(Qry::class))
            ->andReturn($response);
        $response
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($data);

        $view = $this->sut->indexAction();
        $params = $view->getVariables();

        // Assertions
        $this->assertEquals('pages/application-summary', $view->getTemplate());
        $this->assertEquals(123456, $params['licence']);
        $this->assertEquals(3, $params['application']);
        $this->assertEquals($expectedWarningText, $params['warningText']);
        $this->assertEquals($expectedActions, $params['actions']);
    }

    /**
     * @dataProvider indexActionProvider
     */
    public function testPostSubmitSummaryAction(
        $licenceCategory,
        $licenceType,
        $tmResults,
        $expectedWarningText,
        $expectedActions
    ) {
        // Data
        $id = 3;

        $data = [
            'id' => $id,
            'licence' => [
                'id' => 4,
                'licNo' => 123456,
            ],
            'licenceType' => [
                'id' => $licenceType,
            ],
            'goodsOrPsv' => [
                'id' => $licenceCategory,
            ],
            'transportManagers' => $tmResults,
            'status' => [
                'description' => 'some status'
            ],
            'receivedDate' => '2014-01-01',
            'targetCompletionDate' => '2014-02-01',
            'interimStatus' => ['description' => 'Requested'],
            'interimStart' => '2015-02-12',
        ];

        // Mocks
        $response = m::mock();

        // Expectations
        $this->sut->shouldReceive('getIdentifier')
            ->andReturn($id);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(Qry::class))
            ->andReturn($response);
        $response
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->shouldReceive('getResult')
            ->andReturn($data);

        $view = $this->sut->postSubmitSummaryAction();
        $params = $view->getVariables();

        // Assertions
        $this->assertEquals('pages/application-post-submit-summary', $view->getTemplate());
        $this->assertEquals(123456, $params['licence']);
        $this->assertEquals(3, $params['application']);
        $this->assertEquals($expectedWarningText, $params['warningText']);
        $this->assertEquals($expectedActions, $params['actions']);
        $this->assertEquals('some status', $params['status']);
        $this->assertEquals('01 January 2014', $params['submittedDate']);
        $this->assertEquals('01 February 2014', $params['targetCompletionDate']);
    }

    public function indexActionProvider()
    {
        return [
            'GV, SN, No Tms' => [
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                [],
                'markup-summary-warning-variation-goods-application',
                ['markup-summary-application-actions-document']
            ],
            'GV, SN, With Tms' => [
                RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
                RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    ['foo' => 'bar']
                ],
                'markup-summary-warning-variation-goods-application',
                [
                    'summary-application-actions-transport-managers',
                    'markup-summary-application-actions-document'
                ]
            ],
            'PSV, SN, With Tms' => [
                RefData::LICENCE_CATEGORY_PSV,
                RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    ['foo' => 'bar']
                ],
                'markup-summary-warning-variation-psv-application',
                [
                    'summary-application-actions-transport-managers',
                    'markup-summary-application-actions-document'
                ]
            ]
        ];
    }
}
