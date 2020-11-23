<?php

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Controller\Licence\Vehicle\TransferVehicleConfirmationController;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Repository\Licence\LicenceRepository;
use Olcs\Repository\Licence\Vehicle\LicenceVehicleRepository;
use Olcs\Session\LicenceVehicleManagement;
use PHPUnit\Framework\TestCase;
use Zend\Http\Request;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\View\Model\ViewModel;

class TransferVehicleConfirmationControllerTest extends TestCase
{
    /**
     * Creates a new controller instance.
     *
     * @param array $constructorArgs
     * @return TransferVehicleConfirmationController
     */
    protected function newController(array $constructorArgs = [])
    {
        $flashMessenger = $constructorArgs[FlashMessengerHelperService::class]
            ?? $this->getMockBuilder(FlashMessengerHelperService::class)->disableOriginalConstructor()->getMock();
        $translator = $constructorArgs[TranslationHelperService::class]
            ?? $this->getMockBuilder(TranslationHelperService::class)->disableOriginalConstructor()->getMock();
        $session = $constructorArgs[LicenceVehicleManagement::class]
            ?? $this->getMockBuilder(LicenceVehicleManagement::class)->disableOriginalConstructor()->getMock();
        $commandBus = $constructorArgs[HandleCommand::class]
            ?? $this->getMockBuilder(HandleCommand::class)->disableOriginalConstructor()->getMock();
        $formService = $constructorArgs[FormHelperService::class]
            ?? $this->getMockBuilder(FormHelperService::class)->disableOriginalConstructor()->getMock();
        $licenceRepository = $constructorArgs[LicenceRepository::class]
            ?? $this->getMockBuilder(LicenceRepository::class)->disableOriginalConstructor()->getMock();
        $licenceVehicleRepository = $constructorArgs[LicenceVehicleRepository::class]
            ?? $this->getMockBuilder(LicenceVehicleRepository::class)->disableOriginalConstructor()->getMock();
        return new TransferVehicleConfirmationController(
            $flashMessenger, $translator, $session, $commandBus, $formService, $licenceRepository, $licenceVehicleRepository
        );
    }

    /**
     * Creates a new route match instance for the index route.
     *
     * @return RouteMatch
     */
    protected function newIndexRouteMatch()
    {
        $routeMatch = new RouteMatch([
            'licence' => '7',
            'controller' => TransferVehicleConfirmationController::class,
            'action' => 'index',
        ]);
        $routeMatch->setMatchedRouteName('licence/vehicle/transfer/confirm/GET');
        return $routeMatch;
    }

    /**
     * @test
     */
    public function indexAction__isDefined()
    {
        $controller = $this->newController();
        $this->assertTrue(is_callable([$controller, 'indexAction']));
    }

    /**
     * @test
     */
    public function indexAction__returnsAView()
    {
        $queryBus = $this->getMockBuilder(HandleQuery::class)->disableOriginalConstructor()->getMock();

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

        $controller = $this->newController([
            HandleQuery::class => $queryBus,
            LicenceVehicleManagement::class => $session,
            LicenceRepository::class => $licenceRepository,
        ]);

        $response = $controller->indexAction($this->newIndexRouteMatch(), new Request());

        $this->assertInstanceOf(ViewModel::class, $response);
    }

//    /**
//     * @test
//     */
//    public function indexAction__returnsAResponse_withCode200()
//    {
//        $controller = $this->newController();
//        $request = new Request();
//        $response = $controller->indexAction($this->newIndexRouteMatch(), $request);
//        $this->assertEquals(200, $response->getStatusCode());
//    }

//    /**
//     * @test
//     */
//    public function postAction__isDefined()
//    {
//        $controller = $this->newController();
//        $this->assertTrue(is_callable([$controller, 'postAction']));
//    }
}