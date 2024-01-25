<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Form;
use Common\Form\FormValidator;
use Common\Service\Cqrs\Response as QueryResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\View\Helper\Panel;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Element\Select;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Router\Http\RouteMatch;
use Laminas\Session\ManagerInterface;
use Laminas\Session\Storage\StorageInterface;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Controller\Licence\Vehicle\SwitchBoardController;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard;
use Olcs\Session\LicenceVehicleManagement;
use PHPUnit\Framework\TestCase;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard as SwitchBoardForm;

class SwitchBoardControllerTest extends TestCase
{
    protected const VEHICLES_ROUTE = ['lva-licence/vehicles', [], [], true];
    protected const A_DECISION_VALUE = 'A_DECISION_VALUE';

    /**
     * @var FlashMessenger
     */
    private $flashMessenger;

    /**
     * @var FormHelperService
     */
    private $formHelper;

    /**
     * @var HandleQuery
     */
    private $queryHandler;

    /**
     * @var Redirect
     */
    private $redirectHelper;

    /**
     * @var ResponseHelperService
     */
    private $responseHelper;

    /**
     * @var LicenceVehicleManagement
     */
    private $session;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var FormValidator
     */
    private $formValidator;

    private SwitchBoardController $sut;

    protected function setup(): void
    {
         $this->flashMessenger = $this->createMock(FlashMessenger::class);
         $this->formHelper = $this->createMock(FormHelperService::class);
         $this->queryHandler = $this->createMock(HandleQuery::class);
         $this->redirectHelper = $this->createMock(Redirect::class);
         $this->responseHelper = $this->createMock(ResponseHelperService::class);
         $this->session = $this->createMock(LicenceVehicleManagement::class);
         $this->urlHelper = $this->createMock(Url::class);
         $this->formValidator = $this->createMock(FormValidator::class);
    }

    private function createSwitchBoardController()
    {
        return new  SwitchBoardController(
            $this->flashMessenger,
            $this->formHelper,
            $this->queryHandler,
            $this->redirectHelper,
            $this->responseHelper,
            $this->session,
            $this->urlHelper,
            $this->formValidator
        );
    }

    private function radioFieldOptionsMock()
    {
        // Create a mock of the Select class, which is a specific type of form field.
        $selectMock = $this->createMock(Select::class);

        // Configure the mock to simulate the behavior of the unsetValueOption method.
        // When unsetValueOption is called, the mock will return itself to support method chaining.
        $selectMock->method('unsetValueOption')->willReturnCallback(function ($key) use ($selectMock) {
            return $selectMock;
        });

        // Configure the fieldset mock to return the Select mock when the FIELD_OPTIONS_NAME is requested.
        $fieldsetMock = $this->createMock(FieldsetInterface::class);

        // Configure the fieldset mock to return the Select mock when the FIELD_OPTIONS_NAME is requested.
        $fieldsetMock->method('get')
            ->with(SwitchBoardForm::FIELD_OPTIONS_NAME)
            ->willReturn($selectMock);

        // Create a mock of the Form class, which represents the entire form.
        $formMock = $this->createMock(Form::class);

        // Configure the form mock to return the Fieldset mock when the FIELD_OPTIONS_FIELDSET_NAME is requested.
        $formMock->method('get')
            ->with(SwitchBoardForm::FIELD_OPTIONS_FIELDSET_NAME)
            ->willReturn($fieldsetMock);

        // Configure the FormHelperService mock to return the Form mock when createForm is called with SwitchBoardForm class.
        $this->formHelper->method('createForm')
            ->with(SwitchBoardForm::class)
            ->willReturn($formMock);
    }

    protected function setUpSessionManagerMock()
    {
        $sessionStorageMock = $this->createMock(StorageInterface::class);
        $sessionManagerMock = $this->createMock(ManagerInterface::class);
        // Configure the session manager mock to return the session storage mock
        $sessionManagerMock->method('getStorage')->willReturn($sessionStorageMock);

        // Mock for LicenceVehicleManagement to return session manager mock
        $this->session = $this->createMock(LicenceVehicleManagement::class);

        $this->session->method('getManager')->willReturn($sessionManagerMock);
    }

    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $this->sut = $this->createSwitchBoardController();

        // Assert
        $this->assertIsCallable([$this->sut, 'indexAction']);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel()
    {
        $routeMatchMock = $this->createMock(RouteMatch::class);
        $routeMatchMock->method('getParam')
            ->with('licence')
            ->willReturn(1);
        $this->setUpSessionManagerMock();

        $this->queryHandler = $this->setupQueryHandler();
        $this->radioFieldOptionsMock();

        $controller = $this->createSwitchBoardController();
        $result = $controller->indexAction(new Request(), $routeMatchMock);

        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithPanel_WhenFlashMessageHasPanelNamespace()
    {
        $routeMatch = new RouteMatch([]);
        $this->setUpSessionManagerMock();
        $this->queryHandler = $this->setupQueryHandler();
        $this->radioFieldOptionsMock();
        // Create a mock of FlashMessenger
        $this->flashMessenger->method('getMessages')
            ->with('panel')
            ->willReturn(['title']);

        // Assuming you're using one of the methods above to set the FlashMessenger
        $controller = $this->createSwitchBoardController();
        // Execute
        $result = $controller->indexAction(new Request(), $routeMatch);

        $expected = [
            'title' => 'title',
            'theme' => Panel::TYPE_SUCCESS
        ];

        // Assert
        $this->assertSame($expected, $result->getVariable('panel'));
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithPanelBody_WhenFlashMessageHasPanelNamespaceSecondMessage()
    {
        // Setup
        $routeMatch = new RouteMatch([]);
        $this->setUpSessionManagerMock();
        $this->queryHandler = $this->setupQueryHandler();
        $this->radioFieldOptionsMock();

        // Create a mock of FlashMessenger
        $this->flashMessenger->method('getMessages')
            ->with('panel')
            ->willReturn(['title', 'body']);

        // Create the controller with the FlashMessenger mock
        $controller = $this->createSwitchBoardController();

        // Execute
        $result = $controller->indexAction(new Request(), $routeMatch);

        $expected = [
            'title' => 'title',
            'theme' => Panel::TYPE_SUCCESS,
            'body' => 'body',
        ];

        // Assert
        $this->assertSame($expected, $result->getVariable('panel'));
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithBackRouteToLicenceOverview()
    {
        // Setup
        $routeMatch = new RouteMatch([]);
        $expectedUrl = 'licence/overview/link';

        // Create a mock of Url
        $this->urlHelper->method('fromRoute')
            ->with(SwitchBoardController::ROUTE_LICENCE_OVERVIEW, [], [], true)
            ->willReturn($expectedUrl);

        // Set up other necessary mocks
        $this->setUpSessionManagerMock();
        $this->queryHandler = $this->setupQueryHandler();
        $this->radioFieldOptionsMock();

        // Create the controller with the Url mock
        $controller = $this->createSwitchBoardController();

        // Execute
        $result = $controller->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertSame($expectedUrl, $result->getVariable('backLink'));
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithSwitchBoardForm()
    {
        // Setup
        $routeMatch = new RouteMatch([]);

        // Set up necessary mocks
        $this->setUpSessionManagerMock();
        $this->queryHandler = $this->setupQueryHandler();

        // Mock the form and form elements
        $this->radioFieldOptionsMock();

        // Create the controller
        $controller = $this->createSwitchBoardController();

        // Execute
        $result = $controller->indexAction(new Request(), $routeMatch);

        // Assert
        $this->assertInstanceOf(Form::class, $result->getVariable('form'));
    }

    /**
     * @test
     */
    public function indexAction_SwitchBoardOnlyHasAdd_WhenLicenceHasNoVehicles()
    {
        // Setup
        $routeMatch = new RouteMatch([]);
        $this->radioFieldOptionsMock();

        // Mock the necessary components
        $formMock = $this->createMock(Form::class);
        $fieldsetMock = $this->createMock(FieldsetInterface::class);
        $selectMock = $this->createMock(Select::class);

        // Configure the form mock to return the fieldset mock
        $formMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->willReturn($fieldsetMock);

        // Configure the fieldset mock to return the select mock
        $fieldsetMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_NAME)
            ->willReturn($selectMock);

        // Configure the select mock to return an array of options that includes 'Add'
        $expectedOptions = [
            SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD => 'Add',
            // Add other options as needed
        ];
        $selectMock->method('getValueOptions')->willReturn($expectedOptions);

        // Mock the necessary dependencies and methods on the controller
        $this->setUpSessionManagerMock();
        $this->queryHandler->method('__invoke')
            ->with($this->isInstanceOf(Licence::class))
            ->willReturn($this->setUpQueryResponse([
                'activeVehicleCount' => 0,
                'totalVehicleCount' => 0,
                'licNo' => 'OB1234567',
                'isMlh' => true
                ]));

        // Create the controller with the mocked dependencies
        $controller = $this->createSwitchBoardController();

        // Execute
        $controller->indexAction(new Request(), $routeMatch);
        // Assert
        $options = $formMock->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->get(SwitchBoard::FIELD_OPTIONS_NAME)
            ->getValueOptions();

        $this->assertArrayHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT, $options);
        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW, $options);
    }

    /**
     * @test
     */
    public function indexAction_SwitchBoardRemovesTransferOption_WhenLicenceIsNotMLH()
    {
        // Setup
        $routeMatch = new RouteMatch([]);
        $this->radioFieldOptionsMock();
        // Mock the necessary components
        $formMock = $this->createMock(Form::class);
        $fieldsetMock = $this->createMock(FieldsetInterface::class);
        $selectMock = $this->createMock(Select::class);

        // Configure the form mock to return the fieldset mock
        $formMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->willReturn($fieldsetMock);

        // Configure the fieldset mock to return the select mock
        $fieldsetMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_NAME)
            ->willReturn($selectMock);

        // Configure the select mock to return an array of options without 'Transfer'
        $expectedOptions = [
            SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD => 'Add',
            // Add other options as needed
        ];
        $selectMock->method('getValueOptions')->willReturn($expectedOptions);

        // Mock the necessary dependencies and methods on the controller
        $this->setUpSessionManagerMock();
        $this->queryHandler->method('__invoke')
            ->with($this->isInstanceOf(Licence::class))
            ->willReturn($this->setUpQueryResponse([
                'activeVehicleCount' => 0,
                'totalVehicleCount' => 0,
                'licNo' => 'OB1234567',
                'isMlh' => false
            ]));

        // Create the controller with the mocked dependencies
        $controller = $this->createSwitchBoardController();

        // Execute
        $controller->indexAction(new Request(), $routeMatch);

        // Assert
        $options = $formMock->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->get(SwitchBoard::FIELD_OPTIONS_NAME)
            ->getValueOptions();

        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER, $options);
    }

    /**
     * @test
     */
    public function indexAction_SwitchBoardRemovesViewOptionButKeepsViewRemoved_WhenAllVehiclesHaveBeenRemoved()
    {
        // Setup
        $routeMatch = new RouteMatch([]);
        $this->radioFieldOptionsMock();
        // Mock the necessary components
        $formMock = $this->createMock(Form::class);
        $fieldsetMock = $this->createMock(FieldsetInterface::class);
        $selectMock = $this->createMock(Select::class);

        // Configure the form mock to return the fieldset mock
        $formMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->willReturn($fieldsetMock);

        // Configure the fieldset mock to return the select mock
        $fieldsetMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_NAME)
            ->willReturn($selectMock);

        // Configure the select mock to return an array of options without 'View' but with 'View Removed'
        $expectedOptions = [
            SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD => 'Add',
            SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED => 'View Removed',
            // Add other options as needed
        ];
        $selectMock->method('getValueOptions')->willReturn($expectedOptions);

        // Mock the necessary dependencies and methods on the controller
        $this->setUpSessionManagerMock();
        $this->queryHandler->method('__invoke')
            ->with($this->isInstanceOf(Licence::class))
            ->willReturn($this->setUpQueryResponse([
                'activeVehicleCount' => 0,
                'totalVehicleCount' => 1,
                'licNo' => 'OB1234567',
                'isMlh' => false
            ]));

        // Create the controller with the mocked dependencies
        $controller = $this->createSwitchBoardController();

        // Execute
        $controller->indexAction(new Request(), $routeMatch);

        // Assert
        $options = $formMock->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->get(SwitchBoard::FIELD_OPTIONS_NAME)
            ->getValueOptions();

        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW, $options);
        $this->assertArrayHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED, $options);
    }

    /**
     * @test
     */
    public function indexAction_SwitchBoardHasViewOptionRemovesViewRemoved_WhenNoVehiclesHaveBeenRemoved()
    {
        // Setup
        $routeMatch = new RouteMatch([]);
        $this->radioFieldOptionsMock();

        // Mock the necessary components
        $formMock = $this->createMock(Form::class);
        $fieldsetMock = $this->createMock(FieldsetInterface::class);
        $selectMock = $this->createMock(Select::class);

        // Configure the form mock to return the fieldset mock
        $formMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->willReturn($fieldsetMock);

        // Configure the fieldset mock to return the select mock
        $fieldsetMock->method('get')
            ->with(SwitchBoard::FIELD_OPTIONS_NAME)
            ->willReturn($selectMock);

        // Configure the select mock to return an array of options that includes 'View'
        $expectedOptions = [
            SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW => 'View',
            // Add other options as needed
        ];
        $selectMock->method('getValueOptions')->willReturn($expectedOptions);

        // Mock the necessary dependencies and methods on the controller
        $this->setUpSessionManagerMock();
        $this->queryHandler->method('__invoke')
            ->with($this->isInstanceOf(Licence::class))
            ->willReturn($this->setUpQueryResponse([
                'activeVehicleCount' => 1,
                'totalVehicleCount' => 1,
                'licNo' => 'OB1234567',
                'isMlh' => true
            ]));

        // Create the controller with the mocked dependencies
        $controller = $this->createSwitchBoardController();

        // Execute
        $controller->indexAction(new Request(), $routeMatch);

        // Assert
        $options = $formMock->get(SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME)
            ->get(SwitchBoard::FIELD_OPTIONS_NAME)
            ->getValueOptions();

        $this->assertArrayNotHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED, $options);
        $this->assertArrayHasKey(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW, $options);
    }

    /**
     * @test
     */
    public function indexAction_WithPost_ShouldReturnRedirectToIndexAction_WhenFormIsInvalid()
    {
        // Setup
        $this->setUpSessionManagerMock();
        $this->queryHandler = $this->setupQueryHandler();
        $this->radioFieldOptionsMock();

        $expectedResponse = new Response();
        $request = $this->setUpDecisionRequest(static::A_DECISION_VALUE);

        $routeMatch = new RouteMatch([]);

        // Expect
        $this->redirectHelper->method('toRoute')->with(...static::VEHICLES_ROUTE)->willReturn($expectedResponse);
        $controller = $this->createSwitchBoardController();
        // Execute
        $result = $controller->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $result);
    }

    /**
     * @test
     * @dataProvider indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision_Provider
     */
    public function indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision()
    {
        // Setup
        $this->setUpSessionManagerMock();
        $this->queryHandler = $this->setupQueryHandler();
        $this->radioFieldOptionsMock();
        $routeMatch = new RouteMatch([]);

        // Mock the Redirect plugin
        $redirectHelper = $this->createMock(Redirect::class);

        // Define the expected route
        $expectedRoute = 'lva-licence/vehicles';

        // Create a Response object to return
        $expectedResponse = new \Laminas\Http\Response();
        $expectedResponse->setStatusCode(302); // HTTP 302 Found

        // Expect the toRoute method to be called with the expected arguments
        $redirectHelper->expects($this->once())
            ->method('toRoute')
            ->with(
                $this->equalTo($expectedRoute),
                $this->equalTo([]),
                $this->equalTo([]),
                $this->equalTo(true)
            )
            ->willReturn($expectedResponse); // Return the Response object

        // Define expectations for the query handler
        $licenceData = $this->setUpDefaultLicenceData();
        $licenceData['activeVehicleCount'] = 1; // Set the desired active vehicle count
        $licenceData['totalVehicleCount'] = 1;

        $queryHandler = $this->createMock(HandleQuery::class);
        $queryHandler->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(Licence::class))
            ->willReturn($this->setUpQueryResponse($licenceData));

        // Create the controller with the mocked dependencies
        $controller = $this->createSwitchBoardController();

        // Execute
        $request = $this->setUpDecisionRequest(SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD); // Set the desired decision
        $response = $controller->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    public function indexAction_WithPost_ShouldRedirectToPage_DependantOnDecision_Provider()
    {
        return [
            'Add decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_ADD,
                    [],
                    [],
                    true,
                ],
            ],
            'Remove Decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_REMOVE,
                    [],
                    [],
                    true,
                ],
            ],
            'Reprint decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_REPRINT,
                    [],
                    [],
                    true,
                ],
            ],
            'Transfer decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_TRANSFER,
                    [],
                    [],
                    true,
                ],
            ],
            'View decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW,
                1,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_LIST,
                    [],
                    [],
                    true,
                ],
            ],
            'View removed decision' => [
                SwitchBoard::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED,
                0,
                [
                    SwitchBoardController::ROUTE_LICENCE_VEHICLE_LIST,
                    [],
                    [
                        'query' => [
                            ListVehicleController::QUERY_KEY_INCLUDE_REMOVED => ''
                        ],
                        'fragment' => ListVehicleController::REMOVE_TABLE_WRAPPER_ID
                    ],
                    true,
                ],
            ]
        ];
    }

    /**
     * @return MockInterface|Redirect
     */
    protected function redirectHelper(): MockInterface
    {
        if (! $this->serviceManager->has(Redirect::class)) {
            $instance = $this->setUpMockService(Redirect::class);
            $this->serviceManager->setService(Redirect::class, $instance);
        }
        return $this->serviceManager->get(Redirect::class);
    }

    /**
     * @return MockInterface|FormValidator
     */
    protected function formValidator(): MockInterface
    {
        if (! $this->serviceManager->has(FormValidator::class)) {
            $instance = $this->setUpMockService(FormValidator::class);
            $instance->allows('isValid')->andReturnUsing(function ($form) {

                $form->isValid();
                return true;
            })->byDefault();
            $this->serviceManager->setService(FormValidator::class, $instance);
        }
        return $this->serviceManager->get(FormValidator::class);
    }

    /**
     * @return FormHelperService
     */
    protected function formHelper(): FormHelperService
    {
        $formHelperMock = $this->createMock(FormHelperService::class);

        $formHelperMock->method('createForm')
            ->willReturnCallback(function () {
                $annotationBuilder = new AnnotationBuilder();
                return $annotationBuilder->createForm(SwitchBoard::class);
            });

        return $formHelperMock;
    }

    /**
     * @return array
     */
    protected function setUpDefaultLicenceData(): array
    {
        return [
            'id' => 1,
            'licNo' => 'OB1234567',
            'isMlh' => true,
            'activeVehicleCount' => 1,
            'totalVehicleCount' => 2,
        ];
    }

    /**
     * @return HandleQuery|m\LegacyMockInterface|MockInterface
     */
    protected function setupQueryHandler()
    {
        $instance = m::mock(HandleQuery::class);
        $instance->shouldReceive('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Licence::class))
            ->andReturnUsing(function () {
                return $this->setUpQueryResponse(
                    $this->setUpDefaultLicenceData()
                );
            })
            ->byDefault();
        return $instance;
    }

    /**
     * @param mixed $data
     * @return QueryResponse|MockInterface
     */
    protected function setUpQueryResponse(array $data): QueryResponse
    {
        $response = new QueryResponse(new Response());
        $response->setResult($data);
        return $response;
    }

    /**
     * @return Request
     */
    protected function setUpDecisionRequest(string $value): Request
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(
            new Parameters([
                    SwitchBoard::FIELD_OPTIONS_FIELDSET_NAME => [
                        SwitchBoard::FIELD_OPTIONS_NAME => $value
                    ]
            ])
        );
        return $request;
    }
}
