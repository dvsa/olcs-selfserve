<?php

/**
 * Dashboard Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Entity\UserEntityService;

/**
 * Dashboard Controller Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\DashboardController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut->setServiceLocator($this->sm);
    }

    public function dataProviderCorrectDashboardShown()
    {
        return [
            [true, true, true],
            [true, false, true],
            [false, true, false],
            [false, false, true], // this should be impossible as if you don't have either you shouldn't be on the page
        ];
    }

    /**
     * @dataProvider dataProviderCorrectDashboardShown
     * @param type $permissionSelfserveLva
     * @param type $permissionSelfserveTmDashboard
     * @param type $standardView
     */
    public function testCorrectDashboardShown($permissionSelfserveLva, $permissionSelfserveTmDashboard, $standardView)
    {
        $this->sut->shouldReceive('isGranted')
            ->with(UserEntityService::PERMISSION_SELFSERVE_LVA)
            ->andReturn($permissionSelfserveLva);
        $this->sut->shouldReceive('isGranted')
            ->with(UserEntityService::PERMISSION_SELFSERVE_TM_DASHBOARD)
            ->andReturn($permissionSelfserveTmDashboard);

        if ($standardView) {
            $this->sut->shouldReceive('transportManagerDashboardView')->never();
            $this->sut->shouldReceive('standardDashboardView')->once();
        } else {
            $this->sut->shouldReceive('transportManagerDashboardView')->once()->with();
            $this->sut->shouldReceive('standardDashboardView')->never();
        }

        $this->sut->indexAction();
    }

    public function testDashboardStandard()
    {
        $mockApplicationEntity = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationEntity);

        $mockDashboardProcessingService = m::mock();
        $this->sm->setService('DashboardProcessingService', $mockDashboardProcessingService);

        $mockNavigation = m::mock();
        $this->sm->setService('Olcs\Navigation\DashboardNavigation', $mockNavigation);

        $mockFeeService = m::mock();
        $this->sm->setService('Entity\Fee', $mockFeeService);

        $mockCorrespondenceService = m::mock();
        $this->sm->setService('Entity\CorrespondenceInbox', $mockCorrespondenceService);

        $this->sut->shouldReceive('isGranted')
            ->with(UserEntityService::PERMISSION_SELFSERVE_TM_DASHBOARD)
            ->once()
            ->andReturn(true);
        $this->sut->shouldReceive('isGranted')
            ->with(UserEntityService::PERMISSION_SELFSERVE_LVA)
            ->once()
            ->andReturn(true);
        $this->sut->shouldReceive('getCurrentOrganisationId')
            ->with()
            ->andReturn(45);

        $mockApplicationEntity->shouldReceive('getForOrganisation')
            ->with(45)
            ->once()
            ->andReturn(['application data']);

        $mockDashboardProcessingService->shouldReceive('getTables')
            ->with(['application data'])
            ->once()
            ->andReturn(['applications' => ['apps'], 'variations' => ['vars'], 'licences' => ['lics']]);

        $mockFeeService
            ->shouldReceive('getOutstandingFeesForOrganisation')
            ->with(45)
            ->once()
            ->andReturn(
                [
                    'Count' => '3',
                    'Results' => [
                        ['id' => 1, 'description' => 'fee 1'],
                        ['id' => 2, 'description' => 'fee 2'],
                        ['id' => 3, 'description' => 'fee 3'],
                    ],
                ]
            );

        $mockCorrespondenceService
            ->shouldReceive('getCorrespondenceByOrganisation')
            ->with(45)
            ->once()
            ->andReturn(
                [
                    'Count' => '3',
                    'Results' => [
                        ['id' => 1, 'accessed' => 'N'],
                        ['id' => 2, 'accessed' => 'Y'],
                        ['id' => 3, 'accessed' => 'Y'],
                    ],
                ]
            );

        $mockNavigation
            ->shouldReceive('findOneById')
            ->with('dashboard-fees')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 3)
                    ->getMock()
            )
            ->shouldReceive('findOneById')
            ->with('dashboard-correspondence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('set')
                    ->with('count', 1)
                    ->getMock()
            );

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('dashboard', $view->getTemplate());
        $this->assertEquals(['apps'], $view->getVariable('applications'));
        $this->assertEquals(['vars'], $view->getVariable('variations'));
        $this->assertEquals(['lics'], $view->getVariable('licences'));
    }

    public function testDashboardTransportManager()
    {
        $mockUserEntity = m::mock();
        $this->sm->setService('Entity\User', $mockUserEntity);

        $mockTable = m::mock();
        $this->sm->setService('Table', $mockTable);

        $mockDataMapper = m::mock();
        $this->sm->setService('DataMapper\DashboardTmApplications', $mockDataMapper);

        $this->sut->shouldReceive('getLoggedInUser')
            ->once()
            ->andReturn(754);
        $this->sut->shouldReceive('isGranted')
            ->with(UserEntityService::PERMISSION_SELFSERVE_TM_DASHBOARD)
            ->once()
            ->andReturn(true);
        $this->sut->shouldReceive('isGranted')
            ->with(UserEntityService::PERMISSION_SELFSERVE_LVA)
            ->once()
            ->andReturn(false);

        $mockUserEntity->shouldReceive('getTransportManagerApplications')
            ->with(754)
            ->once()
            ->andReturn(['service data']);

        $mockDataMapper->shouldReceive('map')
            ->with(['service data'])
            ->once()
            ->andReturn(['mapped data']);

        $mockTable->shouldReceive('buildTable')
            ->with('dashboard-tm-applications', ['mapped data'])
            ->once()
            ->andReturn('TABLE');

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
        $this->assertEquals('dashboard-tm', $view->getTemplate());
        $this->assertEquals('TABLE', $view->getVariable('applicationsTable'));
    }
}
