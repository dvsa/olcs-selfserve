<?php

namespace OlcsTest\Action\Licence\Vehicle;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Action\Licence\Vehicle\TransferVehicleConfirmationIndexAction;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Exception\Http\NotFoundHttpException;
use Olcs\Repository\Licence\LicenceRepository;
use Olcs\Repository\Licence\Vehicle\LicenceVehicleRepository;
use Olcs\Session\LicenceVehicleManagement;
use PHPUnit\Framework\TestCase;
use Zend\Http\Request;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\View\Model\ViewModel;

/**
 * @see TransferVehicleConfirmationIndexAction
 */
class TransferVehicleConfirmationIndexActionTest extends TestCase
{
    /**
     * @test
     */
    public function indexAction__isDefined()
    {
        $controller = $this->newAction();
        $this->assertTrue(is_callable([$controller, '__invoke']));
    }

    /**
     * @test
     */
    public function indexAction__returnsAView()
    {
        $licenceRepository = $this->getMockBuilder(LicenceRepository::class)->disableOriginalConstructor()->getMock();
        $licenceRepository->expects($this->any())->method('findOneById')->will($this->returnCallback(function ($licenceId) {
            return new LicenceDTO(['id' => $licenceId, 'licNo' => sprintf('LIC%s', $licenceId)]);
        }));
        $licenceVehicleRepository = $this->getMockBuilder(LicenceVehicleRepository::class)->disableOriginalConstructor()->getMock();
        $licenceVehicleRepository->expects($this->any())->method('findByVehicleId')->will($this->returnCallback(function ($vehicleIds) {
            return [new LicenceVehicleDTO(['vehicle' => ['id' => $vehicleIds[0], 'vrm' => 'AA01AAA']])];
        }));
        $session = $this->getMockBuilder(LicenceVehicleManagement::class)->disableOriginalConstructor()->getMock();
        $session->method('getDestinationLicenceId')->willReturn(1);
        $session->method('getVrms')->willReturn([[1]]);
        $action = $this->newAction([
            LicenceVehicleManagement::class => $session,
            LicenceRepository::class => $licenceRepository,
        ]);
        $response = $action->__invoke($this->newIndexRouteMatch(1), new Request());
        $this->assertInstanceOf(ViewModel::class, $response);
    }

    /**
     * @test
     */
    public function indexAction__throwsNotFoundHttpException_ifCurrentLicenceNotFound()
    {
        $licenceRepository = $this->getMockBuilder(LicenceRepository::class)->disableOriginalConstructor()->getMock();
        $licenceRepository->expects($this->any())->method('findOneById')->willReturn(null);
        $action = $this->newAction([LicenceRepository::class => $licenceRepository]);
        $this->expectException(NotFoundHttpException::class);
        $action->__invoke($this->newIndexRouteMatch(1), new Request());
    }

    /**
     * @test
     */
    public function indexAction__returnsRedirectToTransferIndex_ifDestinationLicenceIsNotSetInSession()
    {
        $expectedResult = 'MOCK_RESPONSE';
        $currentLicence = new LicenceDTO(['id' => 1]);
        $licenceRepository = $this->getMockBuilder(LicenceRepository::class)->disableOriginalConstructor()->getMock();
        $licenceRepository->expects($this->any())->method('findOneById')->willReturn($currentLicence);
        $session = $this->getMockBuilder(LicenceVehicleManagement::class)->disableOriginalConstructor()->getMock();
        $session->method('getDestinationLicenceId')->willReturn(null);
        $redirectPlugin = $this->getMockBuilder(Redirect::class)->disableOriginalConstructor()->getMock();
        $redirectPlugin->expects($this->once())
            ->method('toRoute')
            ->with('licence/vehicle/transfer/GET', ['licence' => $currentLicence->getId()])
            ->willReturn($expectedResult);
        $action = $this->newAction([
            LicenceRepository::class => $licenceRepository,
            LicenceVehicleManagement::class => $session,
            Redirect::class => $redirectPlugin,
        ]);
        $result = $action->__invoke($this->newIndexRouteMatch($currentLicence->getId()), new Request());
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Creates a new controller instance.
     *
     * @param array $constructorArgs
     * @return TransferVehicleConfirmationIndexAction
     */
    protected function newAction(array $constructorArgs = [])
    {
        $flashMessenger = $constructorArgs[FlashMessengerHelperService::class]
            ?? $this->getMockBuilder(FlashMessengerHelperService::class)->disableOriginalConstructor()->getMock();
        $translator = $constructorArgs[TranslationHelperService::class]
            ?? $this->getMockBuilder(TranslationHelperService::class)->disableOriginalConstructor()->getMock();
        $session = $constructorArgs[LicenceVehicleManagement::class]
            ?? $this->getMockBuilder(LicenceVehicleManagement::class)->disableOriginalConstructor()->getMock();
        $formService = $constructorArgs[FormHelperService::class]
            ?? $this->getMockBuilder(FormHelperService::class)->disableOriginalConstructor()->getMock();
        $licenceRepository = $constructorArgs[LicenceRepository::class]
            ?? $this->getMockBuilder(LicenceRepository::class)->disableOriginalConstructor()->getMock();
        $licenceVehicleRepository = $constructorArgs[LicenceVehicleRepository::class]
            ?? $this->getMockBuilder(LicenceVehicleRepository::class)->disableOriginalConstructor()->getMock();
        $urlPlugin = $constructorArgs[Url::class]
            ?? $this->getMockBuilder(Url::class)->disableOriginalConstructor()->getMock();
        $redirectPlugin = $constructorArgs[Redirect::class]
            ?? $this->getMockBuilder(Redirect::class)->disableOriginalConstructor()->getMock();
        return new TransferVehicleConfirmationIndexAction(
            $flashMessenger,
            $translator,
            $session,
            $formService,
            $licenceRepository,
            $licenceVehicleRepository,
            $urlPlugin,
            $redirectPlugin
        );
    }

    /**
     * Creates a new route match instance for the index route.
     *
     * @param int $licenceId
     * @return RouteMatch
     */
    protected function newIndexRouteMatch(int $licenceId)
    {
        $routeMatch = new RouteMatch([
            'licence' => $licenceId,
            'controller' => TransferVehicleConfirmationIndexAction::class,
        ]);
        $routeMatch->setMatchedRouteName('licence/vehicle/transfer/confirm/GET');
        return $routeMatch;
    }
}