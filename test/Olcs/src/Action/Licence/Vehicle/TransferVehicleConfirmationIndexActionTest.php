<?php

namespace OlcsTest\Action\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Action\Licence\Vehicle\TransferVehicleConfirmationIndexAction;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
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

        $controller = $this->newAction([
            HandleQuery::class => $queryBus,
            LicenceVehicleManagement::class => $session,
            LicenceRepository::class => $licenceRepository,
        ]);

        $response = $controller->__invoke($this->newIndexRouteMatch(), new Request());

        $this->assertInstanceOf(ViewModel::class, $response);
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
     * @return RouteMatch
     */
    protected function newIndexRouteMatch()
    {
        $routeMatch = new RouteMatch([
            'licence' => '7',
            'controller' => TransferVehicleConfirmationIndexAction::class,
            'action' => 'index',
        ]);
        $routeMatch->setMatchedRouteName('licence/vehicle/transfer/confirm/GET');
        return $routeMatch;
    }
}