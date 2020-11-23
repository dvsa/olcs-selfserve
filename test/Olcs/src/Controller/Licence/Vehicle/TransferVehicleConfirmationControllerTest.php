<?php

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Olcs\Controller\Licence\Vehicle\TransferVehicleConfirmationController;
use Olcs\Session\LicenceVehicleManagement;
use PHPUnit\Framework\TestCase;
use Zend\Http\Response;
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
        $queryBus = $constructorArgs[HandleQuery::class]
            ?? $this->getMockBuilder(HandleQuery::class)->disableOriginalConstructor()->getMock();
        return new TransferVehicleConfirmationController(
            $flashMessenger, $translator, $session, $commandBus, $queryBus, $formService
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

        // @todo this could be shortened by using a repository or query handler, you would just assert a single function call on the repo instead of the query

        $queryBus->method('__invoke')
            ->with($this->callback(function ($query) {
                if ($query instanceof LicenceVehiclesById) {
                    return $query->getIds() === [1];
                }

                if ($query instanceof Licence) {
                    return in_array($query->getId(), [1, 7]);
                }

                return false;
            }))
            ->willReturn($this->returnCallback(function ($query) {
                if ($query instanceof LicenceVehiclesById) {
                    $results = [
                        1 => ['id' => 1, 'licNo' => 'AA01AAA'],
                    ];
                    $mockDestinationLicenceQueryResult = $this->getMockBuilder(\Common\Service\Cqrs\Response::class)->disableOriginalConstructor()->getMock();
                    $mockDestinationLicenceQueryResult->method('getResult')->willReturn($results[$query->getIds()[0]]);
                    return $mockDestinationLicenceQueryResult;
                }

                if ($query instanceof Licence) {
                    $results = [
                        1 => ['id' => 1, 'licNo' => 'LIC1'],
                        7 => ['id' => 7, 'licNo' => 'LIC7'],
                    ];
                    $mockDestinationLicenceQueryResult = $this->getMockBuilder(\Common\Service\Cqrs\Response::class)->disableOriginalConstructor()->getMock();
                    $mockDestinationLicenceQueryResult->method('getResult')->willReturn($results[$query->getId()]);
                    return $mockDestinationLicenceQueryResult;
                }
            }));
        $session = $this->getMockBuilder(LicenceVehicleManagement::class)->disableOriginalConstructor()->getMock();
        $session->method('getDestinationLicenceId')->willReturn(1);
        $session->method('getVrms')->willReturn([[1]]);

        $controller = $this->newController([
            HandleQuery::class => $queryBus,
            LicenceVehicleManagement::class => $session,
        ]);
        $request = new Request();
        $response = $controller->indexAction($this->newIndexRouteMatch(), $request);
        $this->assertInstanceOf(ViewModel::class, $response);
    }

    /**
     * @test
     */
    public function indexAction__returnsAResponse_withCode200()
    {
        $controller = $this->newController();
        $request = new Request();
        $response = $controller->indexAction($this->newIndexRouteMatch(), $request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function postAction__isDefined()
    {
        $controller = $this->newController();
        $this->assertTrue(is_callable([$controller, 'postAction']));
    }
}