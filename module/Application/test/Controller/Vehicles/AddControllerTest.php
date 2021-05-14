<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicles;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Form\Form;
use Common\Service\Cqrs\Response as QueryResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Dvsa\Olcs\Application\Session\Vehicles;
use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Http\Request;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
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
        $serviceManager = $this->setUpServiceManager();
        $this->setUpSut($serviceManager);

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
        $serviceManager = $this->setUpServiceManager();
        $this->setUpSut($serviceManager);

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
        $serviceManager = $this->setUpServiceManager();
        $this->setUpSut($serviceManager);
        $expectedUrl = 'application/vehicles/ocrs';

        // Define Expectations
        $urlHelper = $this->serviceManager()->get(Url::class);
        $urlHelper->shouldReceive('fromRoute')
            ->with(AddController::ROUTE_APPLICATION_VEHICLES_OCRS, [], [], true)
            ->andReturn($expectedUrl);

        // Execute
        $result = $this->sut->indexAction(new Request());

        // Assert
        $this->assertSame($expectedUrl, $result->getVariable('backLink'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithVehicleSearchForm()
    {
        //Setup
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        // Execute
        $result = $this->sut->indexAction(new Request());

        // Assert
        $this->assertInstanceOf(Form::class, $result->getVariable('searchForm'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithoutVehicleData_WhenRequestIsNotPost()
    {
        //Setup
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        $request = new Request();
        $request->setMethod(Request::METHOD_GET);

        // Execute
        $result = $this->sut->indexAction($request);

        // Assert
        $this->assertEmpty($result->getVariable('vehicleData'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithoutVehicleData_WhenRequestIsNotPost
     */
    public function indexAction_ReturnsViewModel_WithoutVehicleData_WhenSearchFormIsNotValid()
    {
        //Setup
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        $request = $this->setUpPostRequest('VRM1');

        // Execute
        $result = $this->sut->indexAction($request);

        // Assert
        $this->assertEmpty($result->getVariable('vehicleData'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithoutVehicleData_WhenSearchFormIsNotValid
     */
    public function indexAction_ReturnsViewModel_WithoutVehicleData_WhenVehicleDataIsNotPresent()
    {
        //Setup
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        $request = $this->setUpPostRequest(static::VALID_VRM);

        // Execute
        $result = $this->sut->indexAction($request);

        // Assert
        $this->assertEmpty($result->getVariable('vehicleData'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithVehicleData_WhenVehicleDataPresent()
    {
        //Setup
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        $session = $this->serviceManager->get(Vehicles::class);
        assert($session instanceof Vehicles, 'expected $session to be instance of ' . Vehicles::class);
        $session->setVehicleData($this->setUpDefaultVehicleData());

        // Execute
        $result = $this->sut->indexAction($this->setUpPostRequest(static::VALID_VRM));

        // Assert
        $this->assertEquals($this->setUpDefaultVehicleData(), $result->getVariable('vehicleData'));
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithVehicleData_WhenVehicleDataPresent
     */
    public function indexAction_ReturnsViewModel_WithConfirmationForm_WhenVehicleDataPresent()
    {
        //Setup
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

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
        $this->assertInstanceOf(Form::class, $result->getVariable('confirmationForm'));
    }

    /**
     * @test
     */
    public function searchAction_IsCallable()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $this->setUpSut($serviceManager);

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
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

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
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        $request = $this->setUpPostRequest(static::VALID_VRM);

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
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        $request = $this->setUpPostRequest(static::VALID_VRM);

        $queryHandler = $this->serviceManager->get(HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Vehicle::class))
            ->andReturn($this->setUpQueryResponse(['count' => 0,'result' => []]));

        // Execute
        $result = $this->sut->searchAction($request);

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
        $serviceManager = $this->setUpServiceManager();
        $this->setupSut($serviceManager);

        $request = $this->setUpPostRequest(static::VALID_VRM);

        $queryHandler = $this->serviceManager->get(HandleQuery::class);
        $queryHandler->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Vehicle::class))
            ->andReturn($this->setUpQueryResponse(['count' => 0,'result' => []], 400));

        // Execute
        $result = $this->sut->searchAction($request);

        // Assert
        $flashMessenger = $this->serviceManager->get(FlashMessenger::class);
        assert($flashMessenger instanceof FlashMessenger, 'expected $flashMessenger to be instance of ' . FlashMessenger::class);

        $this->assertNotEmpty($flashMessenger->getErrorMessages());

    }


    /**
     * @param ServiceManager $serviceManager
     */
    protected function setupSut(ServiceManager $serviceManager)
    {
        $this->sut = new AddController(
            $serviceManager->get(HandleCommand::class),
            $serviceManager->get(FlashMessenger::class),
            $serviceManager->get(FormHelperService::class),
            $serviceManager->get(HandleQuery::class),
            $serviceManager->get(Redirect::class),
            $serviceManager->get(Vehicles::class),
            $serviceManager->get(TranslationHelperService::class),
            $serviceManager->get(Url::class)
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

    protected function tearDown(): void
    {
        $this->serviceManager->get(Vehicles::class)->destroy();
        parent::tearDown();
    }
}

