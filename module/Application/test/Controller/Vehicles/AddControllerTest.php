<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicles;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Form\Form;
use Common\Service\Cqrs\Response as QueryResponse;
use Common\Service\Cqrs\Response as CommandResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Dvsa\Olcs\Application\Controller\Vehicles\Factory\AddControllerFactory;
use Dvsa\Olcs\Application\Session\Vehicles;
use Dvsa\Olcs\Transfer\Command\Application\CreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Http\Request;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Olcs\Form\Model\Form\Vehicle\AddVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\ConfirmVehicle;

/**
 * @see AddController
 */
class AddControllerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const APPLICATION_ID = 'APPLICATION ID';
    protected const APPLICATION_ID_ROUTE_PARAMETER_NAME = 'application';
    protected const VALID_VRM = 'AB129';
    protected const INVALID_VRM = 'VRM1';

    protected const VARIABLE_VEHICLE_DATA = 'vehicleData';
    protected const VARIABLE_BACKLINK = 'backLink';
    protected const VARIABLE_SEARCH_FORM = 'searchForm';
    protected const VARIABLE_CONFIRMATION_FORM = 'confirmationForm';

    protected const EXPECTED_ERROR_FLASH_MESSAGE_KEY = 'licence.vehicle.add.search.query-error';
    protected const EXPECTED_ERROR_FLASH_MESSAGE = 'licence.vehicle.add.search.query-error_translated';
    protected const EXPECTED_SUCCESS_FLASH_MESSAGE_KEY = 'licence.vehicle.add.success';
    protected const EXPECTED_SUCCESS_FLASH_MESSAGE = 'licence.vehicle.add.success_translated';
    protected const EXPECTED_NOT_DUPLICATE_ERROR_MESSAGE = 'Error adding vehicle';

    /**
     * @var AddController
     */
    protected $sut;

    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();

        var_dump($this->sut);

        // Assert
        $this->assertIsCallable([$this->sut, 'indexAction']);
    }

    /**
     * @test
     * @depends indexAction_IsCallable
     */
    public function indexAction_ReturnsViewModel()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();

        // Execute
        $result = $this->sut->indexAction(new Request());

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithBackRouteToLicenceOverview()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();
        $expectedUrl = 'application/vehicles/ocrs';

        // Define Expectations
        $urlHelper = $this->serviceManager()->get(Url::class);
        $urlHelper->shouldReceive('fromRoute')
            ->with(AddController::ROUTE_APPLICATION_VEHICLES_OCRS, [], [], true)
            ->andReturn($expectedUrl);

        // Execute
        $result = $this->sut->indexAction(new Request());

        // Assert
        $this->assertSame($expectedUrl, $result->getVariable(static::VARIABLE_BACKLINK));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithVehicleSearchForm()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();

        // Execute
        $result = $this->sut->indexAction(new Request());

        // Assert
        $this->assertInstanceOf(Form::class, $result->getVariable(static::VARIABLE_SEARCH_FORM));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithoutVehicleData_WhenRequestIsNotPost()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();

        $request = new Request();
        $request->setMethod(Request::METHOD_GET);

        // Execute
        $result = $this->sut->indexAction($request);

        // Assert
        $this->assertEmpty($result->getVariable(static::VARIABLE_VEHICLE_DATA));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithoutVehicleData_WhenRequestIsNotPost
     */
    public function indexAction_ReturnsViewModel_WithoutVehicleData_WhenSearchFormIsNotValid()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();
        $request = $this->setUpPostRequest(static::INVALID_VRM);

        // Execute
        $result = $this->sut->indexAction($request);

        // Assert
        $this->assertEmpty($result->getVariable(static::VARIABLE_VEHICLE_DATA));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithoutVehicleData_WhenSearchFormIsNotValid
     */
    public function indexAction_ReturnsViewModel_WithoutVehicleData_WhenVehicleDataIsNotPresent()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();
        $request = $this->setUpPostRequest(static::VALID_VRM);

        // Execute
        $result = $this->sut->indexAction($request);

        // Assert
        $this->assertEmpty($result->getVariable(static::VARIABLE_VEHICLE_DATA));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithVehicleData_WhenVehicleDataPresent()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();
        $session = $this->serviceManager->get(Vehicles::class);
        assert($session instanceof Vehicles, 'expected $session to be instance of ' . Vehicles::class);
        $session->setVehicleData($this->setUpDefaultVehicleData());

        // Execute
        $result = $this->sut->indexAction($this->setUpPostRequest(static::VALID_VRM));

        // Assert
        $this->assertEquals($this->setUpDefaultVehicleData(), $result->getVariable(static::VARIABLE_VEHICLE_DATA));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithVehicleData_WhenVehicleDataPresent
     */
    public function indexAction_ReturnsViewModel_WithConfirmationForm_WhenVehicleDataPresent()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();

        // Define Expectations
        $formHelper = $this->serviceManager->get(FormHelperService::class);
        $formHelper->shouldReceive('createForm')
            ->with(ConfirmVehicle::class)
            ->andReturnUsing(function () {
                $annotationBuilder = new AnnotationBuilder();
                $form = $annotationBuilder->createForm(ConfirmVehicle::class);
                return $form;
            });

        $session = $this->serviceManager->get(Vehicles::class);
        assert($session instanceof Vehicles, 'expected $session to be instance of ' . Vehicles::class);
        $session->setVehicleData($this->setUpDefaultVehicleData());

        // Execute
        $result = $this->sut->indexAction($this->setUpPostRequest(static::VALID_VRM));

        // Assert
        $this->assertInstanceOf(Form::class, $result->getVariable(static::VARIABLE_CONFIRMATION_FORM));
    }

    /**
     * @test
     */
    public function searchAction_IsCallable()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'searchAction']);
    }

    /**
     * @test
     * @depends searchAction_IsCallable
     */
    public function searchAction_ShouldReturnIndexAction()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();
        $request = $this->setUpPostRequest(static::VALID_VRM);

        // Execute
        $result = $this->sut->searchAction($request);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function searchAction_ShouldSetVehicleData_WhenVehicleDataIsFound()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();
        $request = $this->setUpPostRequest(static::VALID_VRM);

        // Define Expectations
        $queryHandler = $this->serviceManager->get(HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(Vehicle::class)
            ->andReturn($this->setUpQueryResponse(['count' => 1, 'results' => [$this->setUpDefaultVehicleData()]]));

        // Execute
        $this->sut->searchAction($request);

        // Assert
        $session = $this->serviceManager->get(Vehicles::class);
        assert($session instanceof Vehicles, 'expected $session to be instance of ' . Vehicles::class);
        $this->assertTrue($session->hasVehicleData());
        $this->assertEquals($this->setUpDefaultVehicleData(), $session->getVehicleData());

    }

    /**
     * @test
     * @depends searchAction_IsCallable
     */
    public function searchAction_ShouldMarkVehicleNotFound_WhenVehicleDataIsNotFound()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();
        $request = $this->setUpPostRequest(static::VALID_VRM);

        // Define Expectations
        $queryHandler = $this->serviceManager->get(HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Vehicle::class))
            ->andReturn($this->setUpQueryResponse(['count' => 0,'result' => []]));

        // Execute
        $this->sut->searchAction($request);

        // Assert
        $session = $this->serviceManager->get(Vehicles::class);
        assert($session instanceof Vehicles, 'expected $session to be instance of ' . Vehicles::class);
        $this->assertTrue($session->wasVehicleNotFound());
        $this->assertEmpty($session->getVehicleData());
    }

    /**
     * @test
     * @depends searchAction_IsCallable
     */
    public function searchAction_ShouldFlashMessage_WhenErrorGettingVehicleData()
    {
        //Setup
        $this->setUpServiceManager();
        $this->setupSut();
        $request = $this->setUpPostRequest(static::VALID_VRM);

        // Define Expectations
        $queryHandler = $this->serviceManager->get(HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Vehicle::class))
            ->andReturn($this->setUpQueryResponse(['count' => 0,'result' => []], 400));

        $translator = $this->serviceManager->get(TranslationHelperService::class);
        $translator->shouldReceive('translate')->with(static::EXPECTED_ERROR_FLASH_MESSAGE_KEY)->once()->andReturn(static::EXPECTED_ERROR_FLASH_MESSAGE);

        $flashMessenger = $this->serviceManager->get(FlashMessenger::class);
        $flashMessenger->shouldReceive('addErrorMessage')->with(static::EXPECTED_ERROR_FLASH_MESSAGE)->once();

        // Execute
        $this->sut->searchAction($request);
    }

    /**
     * @test
     */
    public function confirmationAction_IsCallable()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'confirmationAction']);
    }

    /**
     * @test
     * @depends confirmationAction_IsCallable
     */
    public function confirmationAction_ReturnsIndexAction_WhenVehicleDataNotPresent()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();

        // Define expectations
        $expectedResponse = $this->setUpRedirect(AddController::ROUTE_APPLICATION_VEHICLES_ADD);

        // Execute
        $response = $this->sut->confirmationAction(new Request(), new RouteMatch([]));

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function confirmationAction_DestroysVehicleSession()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();
        $this->setUpRedirect(AddController::ROUTE_APPLICATION_VEHICLES);
        $session = $this->setUpSession();

        $commandHandler = $this->serviceManager->get(HandleCommand::class);
        $commandHandler->shouldReceive('__invoke')
            ->with(CreateGoodsVehicle::class)
            ->andReturn($this->setUpCommandResponse([]));

        // Execute
        $this->sut->confirmationAction(new Request(), new RouteMatch([]));

        // Assert
        $this->assertFalse($session->hasVehicleData());

    }

    /**
     * @test
     */
    public function confirmationAction_AddsSuccessFlashMessage_WhenVehicleIsAdded()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();
        $this->setUpRedirect(AddController::ROUTE_APPLICATION_VEHICLES);
        $this->setUpSession();

        $commandHandler = $this->serviceManager->get(HandleCommand::class);
        $commandHandler->shouldReceive('__invoke')
            ->with(CreateGoodsVehicle::class)
            ->andReturn($this->setUpCommandResponse([]));

        // Define Expectations
        $translator = $this->serviceManager->get(TranslationHelperService::class);
        $translator->shouldReceive('translateReplace')->with(static::EXPECTED_SUCCESS_FLASH_MESSAGE_KEY, [static::VALID_VRM])->once()->andReturn(static::EXPECTED_SUCCESS_FLASH_MESSAGE);

        $flashMessenger = $this->serviceManager->get(FlashMessenger::class);
        $flashMessenger->shouldReceive('addSuccessMessage')->with(static::EXPECTED_SUCCESS_FLASH_MESSAGE)->once();

        // Execute
        $this->sut->confirmationAction(new Request(), new RouteMatch([]));
    }

    /**
     * @test
     * @depends confirmationAction_AddsSuccessFlashMessage_WhenVehicleIsAdded
     */
    public function confirmationAction_RedirectsToVehiclesPage_WhenVehicleIsAdded()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();
        $this->setUpSession();

        // Define expectations
        $expectedResponse = $this->setUpRedirect(AddController::ROUTE_APPLICATION_VEHICLES);

        $commandHandler = $this->serviceManager->get(HandleCommand::class);
        $commandHandler->shouldReceive('__invoke')
            ->with(CreateGoodsVehicle::class)
            ->andReturn($this->setUpCommandResponse([]));

        // Execute
        $response = $this->sut->confirmationAction(new Request(), new RouteMatch([]));

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function confirmationAction_AddsErrorFlashMessage_WhenErrorAddingVehicle_ThatIsNotDuplicate()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();
        $this->setUpRedirect(AddController::ROUTE_APPLICATION_VEHICLES_ADD);
        $this->setUpSession();

        $commandHandler = $this->serviceManager->get(HandleCommand::class);
        $commandHandler->shouldReceive('__invoke')
            ->with(CreateGoodsVehicle::class)
            ->andReturn($this->setUpCommandResponse(['messages' => ['vrm' => [static::EXPECTED_NOT_DUPLICATE_ERROR_MESSAGE]]], 400));

        // Define Expectations
        $flashMessenger = $this->serviceManager->get(FlashMessenger::class);
        $flashMessenger->shouldReceive('addErrorMessage')->with(static::EXPECTED_NOT_DUPLICATE_ERROR_MESSAGE)->once();

        // Execute
        $this->sut->confirmationAction(new Request(), new RouteMatch([]));
    }

    /**
     * @test
     * @depends confirmationAction_AddsErrorFlashMessage_WhenErrorAddingVehicle_ThatIsNotDuplicate
     */
    public function confirmationAction_RedirectsToAddVehiclePage_WhenErrorAddingVehicle_ThatIsNotDuplicate()
    {
        // Setup
        $this->setUpServiceManager();
        $this->setUpSut();
        $this->setUpSession();

        $commandHandler = $this->serviceManager->get(HandleCommand::class);
        $commandHandler->shouldReceive('__invoke')
            ->with(CreateGoodsVehicle::class)
            ->andReturn($this->setUpCommandResponse(['messages' => ['vrm' => [static::EXPECTED_NOT_DUPLICATE_ERROR_MESSAGE]]], 400));

        // Define Expectations
        $expectedResponse = $this->setUpRedirect(AddController::ROUTE_APPLICATION_VEHICLES_ADD);

        // Execute
        $response = $this->sut->confirmationAction(new Request(), new RouteMatch([]));

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    /**
     * @param ServiceManager $serviceManager
     */
    protected function setupSut()
    {
        $this->sut = new AddController(
            $this->serviceManager->get(HandleCommand::class),
            $this->serviceManager->get(FlashMessenger::class),
            $this->serviceManager->get(FormHelperService::class),
            $this->serviceManager->get(HandleQuery::class),
            $this->serviceManager->get(Redirect::class),
            $this->serviceManager->get(Vehicles::class),
            $this->serviceManager->get(TranslationHelperService::class),
            $this->serviceManager->get(Url::class)
        );
    }

    /**
     * @param ServiceManager $serviceManager
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager)
    {
        $serviceManager->setService(FormHelperService::class, $this->setUpFormHelper());
        $serviceManager->setService(TranslationHelperService::class,
            $this->setUpMockService(TranslationHelperService::class));
        $serviceManager->setService(Vehicles::class, new Vehicles());
        $serviceManager->setService(HandleCommand::class, $this->setUpMockService(HandleCommand::class));
        $serviceManager->setService(HandleQuery::class, $this->setUpMockService(HandleQuery::class));
        $serviceManager->setService(Url::class, $this->setUpMockService(Url::class));
        $serviceManager->setService(Redirect::class, $this->setUpMockService(Redirect::class));
        $serviceManager->setService(FlashMessenger::class, $this->setUpMockService(FlashMessenger::class));
    }

    /**
     * @return FormHelperService
     */
    protected function setUpFormHelper(): FormHelperService
    {
        $instance = $this->setUpMockService(FormHelperService::class);
        $instance->shouldReceive('createForm')->andReturnUsing(function () {
            $annotationBuilder = new AnnotationBuilder();
            $form = $annotationBuilder->createForm(AddVehicleSearch::class);
            return $form;
        })->byDefault();
        return $instance;
    }

    /**
     * @param mixed $data
     * @param int $statusCode
     * @return QueryResponse|MockInterface
     */
    protected function setUpQueryResponse(array $data, int $statusCode = 200): QueryResponse
    {
        $httpResponse = new HttpResponse();
        $httpResponse->setStatusCode($statusCode);

        $queryResponse = new QueryResponse($httpResponse);
        $queryResponse->setResult($data);

        return $queryResponse;
    }

    /**
     * @param array $data
     * @param int $statusCode
     * @return CommandResponse
     */
    protected function setUpCommandResponse(array $data, int $statusCode = 200): CommandResponse
    {
        $httpResponse = new HttpResponse();
        $httpResponse->setStatusCode($statusCode);

        $commandResponse = new CommandResponse($httpResponse);
        $commandResponse->setResult($data);

        return $commandResponse;
    }

    /**
     * @return Request
     */
    protected function setUpPostRequest(string $value): Request
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(
            new Parameters([AddVehicleSearch::FORM_NAME => [AbstractInputSearch::ELEMENT_INPUT_NAME => $value]])
        );
        return $request;
    }

    protected function setUpDefaultVehicleData()
    {
        return ['registrationNumber' => static::VALID_VRM, 'revenueWeight' => 2300];
    }

    /**
     * @param string $route
     * @return HttpResponse
     */
    protected function setUpRedirect(string $route): HttpResponse
    {
        $expectedResponse = new HttpResponse();

        $redirectHelper = $this->serviceManager->get(Redirect::class);
        $redirectHelper->shouldReceive('toRoute')
            ->with($route, [], [], true)
            ->andReturn($expectedResponse);

        return $expectedResponse;
    }

    protected function tearDown(): void
    {
        $this->serviceManager->get(Vehicles::class)->destroy();
        parent::tearDown();
    }

    /**
     * @return Vehicles
     */
    protected function setUpSession(): Vehicles
    {
        $session = $this->serviceManager->get(Vehicles::class);
        assert($session instanceof Vehicles, 'expected $session to be instance of ' . Vehicles::class);
        $session->setVehicleData($this->setUpDefaultVehicleData());
        return $session;
    }
}

