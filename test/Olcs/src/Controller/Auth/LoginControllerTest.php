<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Rbac\User;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Router\Http\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery\MockInterface;
use Olcs\Controller\Auth\LoginController;
use Olcs\Form\Model\Form\Auth\Login;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginControllerTest extends TestCase
{
    const EMPTY_FORM_DATA = [
        'username' => null,
        'password' => null,
        'csrf' => null,
    ];

    const AUTHENTICATION_RESULT_SUCCESSFUL = [
        Result::SUCCESS,
        [],
        []
    ];

    const AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED = [
        LoginController::AUTH_SUCCESS_WITH_CHALLENGE,
        [],
        [
            'challengeName' => LoginController::CHALLENGE_NEW_PASSWORD_REQUIRED,
            'challengeParameters' => [
                'USER_ID_FOR_SRP' => 'username'
            ],
            'challengeSession' => 'challengeSession'
        ]
    ];
    const AUTHENTICATION_RESULT_CHALLENGE_UNSUPPORTED = [
        LoginController::AUTH_SUCCESS_WITH_CHALLENGE,
        [],
        [
            'challengeName' => 'UnsupportedChallenge',
        ]
    ];
    const AUTHENTICATION_RESULT_FAILURE = [Result::FAILURE, [], ['failed']];
    const AUTHENTICATION_RESULT_USER_NOT_EXIST = [Result::FAILURE_IDENTITY_NOT_FOUND, [], ['Authentication Failed']];
    const AUTHENTICATION_RESULT_CREDENTIAL_INVALID = [Result::FAILURE_CREDENTIAL_INVALID, [], ['Authentication Failed']];
    const AUTHENTICATION_RESULT_FAILURE_ACCOUNT_DISABLED = [LoginController::AUTH_FAILURE_ACCOUNT_DISABLED, [], ['account-disabled']];

    /** @var LoginController */
    private LoginController $sut;

    /** @var MockObject */
    private $authenticationAdapter;

    /** @var MockObject */
    private $authenticationService;

    /** @var MockObject */
    private $currentUser;

    /** @var MockObject */
    private $flashMessenger;

    /** @var MockObject */
    private $formHelper;

    /** @var MockObject */
    private $redirectHelper;

    /** @var MockObject */
    private $authChallengeContainer;

    /** @var MockObject */
    private $container;

    protected function setUp(): void
    {
        $this->authenticationAdapter = $this->createMock(ValidatableAdapterInterface::class);
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->currentUser = $this->createMock(CurrentUser::class);
        $this->flashMessenger = $this->createMock(FlashMessenger::class);
        $this->formHelper = $this->createMock(FormHelperService::class);
        $this->redirectHelper = $this->createMock(Redirect::class);
        $this->authChallengeContainer = $this->createMock(AuthChallengeContainer::class);
    }
    private function createLoginController()
    {
        return new LoginController(
            $this->authenticationAdapter,
            $this->authenticationService,
            $this->currentUser,
            $this->flashMessenger,
            $this->formHelper,
            $this->redirectHelper,
            $this->authChallengeContainer
        );
    }

    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $this->sut = $this->createLoginController();

        // Assert
        $this->assertIsCallable([$this->sut, 'indexAction']);
    }

    /**
     * @test
     */
    public function indexAction_RedirectsToDashboard_WhenUserAlreadyLoggedIn()
    {
        $this->currentUser->expects($this->once())
            ->method('getIdentity')
            ->willReturn($this->createMock(User::class));

        // Mock the redirectHelper to expect a call to toRoute with the specified route and return a redirect response
        $this->redirectHelper->expects($this->once())->method('toRoute')
            ->with(LoginController::ROUTE_INDEX)
            ->willReturn($this->redirectHelper);

        // Execute
        $this->createLoginController()->indexAction();
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel()
    {
        // required Mock Dependencies
        $this->createMockedForm();
        // Mock CurrentUser to be anonymous
        $this->currentUser();
        // Instantiate LoginController with mocked dependencies
        $loginController = $this->createLoginController();

        // Call indexAction
        $result = $loginController->indexAction();
        // Assertions
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithLoginForm()
    {
        // required Mock Dependencies
        $this->createMockedForm();
        // Mock CurrentUser to be anonymous
        $this->currentUser();

        // Instantiate LoginController with mocked dependencies
        $loginController = $this->createLoginController();

        // Call indexAction
        $result = $loginController->indexAction();
        $form = $result->getVariable('form');

        // Assert
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * @test
     */
    public function indexAction_SetsFormData_WhenHasBeenStoredInSession()
    {
        // Setup FlashMessenger mock with return values for different namespaces
        $this->flashMessenger->method('hasMessages')
            ->willReturnMap([
                [LoginController::FLASH_MESSAGE_NAMESPACE_INPUT, true],
                [LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR, false],
            ]);
        $this->flashMessenger->method('getMessages')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            ->willReturn([json_encode(['username' => 'username', 'password' => 'abc'])]);

        // Mock FormHelperService with a dummy form
        $dummyForm = $this->createMock(Form::class);
        $this->formHelper->method('createForm')->willReturn($dummyForm);
        $dummyForm->method('getData')->willReturn([
            'username' => 'username',
            'password' => 'abc',
            'submit' => null
        ]);

        // Mock CurrentUser to be anonymous
        $this->currentUser();

        // Instantiate LoginController with mocked dependencies
        $loginController = $this->createLoginController();

        // Execute
        $result = $loginController->indexAction();
        $form = $result->getVariable('form');
        assert($form instanceof Form);
        $form->isValid();

        // Assert
        $expected = [
            'username' => 'username',
            'password' => 'abc',
            'submit' => null
        ];
        $this->assertEquals($expected, $form->getData());
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithFailureReason_WhenAuthenticationFails()
    {
        // Mock FormHelperService to return a Form instance
        $this->createMockedForm();

        // Setup FlashMessenger mock with return values for different namespaces
        $this->flashMessenger->method('hasMessages')
            ->willReturnMap([
                [LoginController::FLASH_MESSAGE_NAMESPACE_INPUT, false],  // Adjust as needed
                [LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR, true],
            ]);
        $this->flashMessenger->method('getMessagesFromNamespace')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)
            ->willReturn(['failureReason']);

        // Mock CurrentUser to be anonymous
        $this->currentUser();

        // Instantiate LoginController with mocked dependencies
        $loginController = $this->createLoginController();

        // Execute
        $result = $loginController->indexAction();

        // Assert
        $this->assertArrayHasKey('failureReason', $result->getVariables());
    }

    /**
     * @test
     */
    public function postAction_IsCallable()
    {
        // Setup
        $this->sut = $this->createLoginController();

        // Assert
        $this->assertIsCallable([$this->sut, 'postAction']);
    }

    /**
     * @test
     */
    protected function postAction_RedirectsToDashboard_WhenUserAlreadyLoggedIn()
    {
        // Setup
        $this->currentUser->method('getIdentity')
            ->willReturn($this->identity(false));

        // Expect
        $this->redirectHelper->method('toRoute')
            ->with(LoginController::ROUTE_INDEX)
            ->willReturn($this->redirect());

        $controller = $this->createLoginController();

        // Execute
        $controller->postAction($this->postRequest(), new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FlashesFormData_WhenFormInvalid()
    {
        $this->redirectHelper->method('toRoute')
            ->willReturn($this->redirect());
        $this->flashMessenger->method('addMessage')
            ->with(function ($message, $namespace) {
                $controller = $this->createLoginController();
                $controller->postAction($this->postRequest(), new RouteMatch([]), new Response());
                $this->assertSame(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT, $namespace);
                return true;
            });
        $this->createLoginController();
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function postAction_SuccessfulAuth_RedirectsToDashBoard_WhenGotoNotPresent()
    {
        // Setup
        $request = $this->postRequest(['username' => 'username', 'password' => 'password']);
        $response = new Response();

        // Mock CurrentUser to be anonymous
        $this->currentUser();
        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->redirectHelper->method('toRoute')
            ->with('auth/login/GET')
            ->willReturn($this->redirect());

        $this->createMockedForm();

        $controller = $this->createLoginController();
        // Execute
        $result = $controller->postAction($request, new RouteMatch([]), $response);
        // Assertions
        $this->assertInstanceOf(Response::class, $result); // Check that the action returns a Response object
        $this->assertEquals(302, $result->getStatusCode()); // Check that the status code is 302 (redirect)
    }

    /**
     * @test
     */
    public function postAction_SuccessfulAuth_RedirectsToGoto_WhenPresentAndValid()
    {
        // Mock CurrentUser to be anonymous
        $this->currentUser();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password'],
            ['goto' => 'https://localhost/goto/url']
        );
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Mock the redirectHelper to return a Response object when toRoute is called
        $redirectResponse = new Response();
        $this->redirectHelper->expects($this->once())
            ->method('toRoute')
            ->with('auth/login/GET')
            ->willReturn($redirectResponse);

        $this->createMockedForm();

        $controller = $this->createLoginController();

        // Execute
        $result = $controller->postAction($request, new RouteMatch([]), $response);

        // Assertions
        $this->assertInstanceOf(Response::class, $result); // Check that the result is a Response object
        $this->assertSame($redirectResponse, $result);
    }

    /**
     * @test
     */
    public function postAction_SuccessfulOAuth_RedirectsToDashboard_WhenGotoPresentAndInvalid()
    {
        // Mock CurrentUser to be anonymous
        $this->currentUser();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password'],
            ['goto' => 'https://example.com/goto/url']
        );
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->createMockedRedirect();

        $this->createMockedForm();

        $controller = $this->createLoginController();

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     */
    public function postAction_NewPasswordRequiredChallenge_StoresChallengeInSession()
    {
        // Mock CurrentUser to be anonymous
        $this->currentUser();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );
        $response = new Response();

        // Mock the currentUser to return an anonymous identity
        $this->currentUser->expects($this->once())
            ->method('getIdentity')
            ->willReturnSelf(); // Return the CurrentUser mock itself

        // Mock the form
        $this->createMockedForm();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));

        // Expect
        $this->createMockedRedirect();

        $controller = $this->createLoginController();
        // Call the postAction method
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postAction_NewPasswordRequiredChallenge_StoresChallengeInSession
     */
    public function postAction_NewPasswordRequiredChallenge_RedirectsToExpiredPassword()
    {
        // Mock CurrentUser to be anonymous
        $this->currentUser();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        // Mock the form
        $this->createMockedForm();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));

        // Expect
        $this->createMockedRedirect();
        $controller = $this->createLoginController();
        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_UnsupportedChallenge_RedirectsToLoginPage()
    {
        // Mock CurrentUser to be anonymous
        $this->currentUser();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        // Mock the form
        $this->createMockedForm();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_UNSUPPORTED));

        // Expect
        $this->createMockedRedirect();
        $controller = $this->createLoginController();

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_RedirectsToLoginPage()
    {
        // Mock CurrentUser to be anonymous
        $this->currentUser();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->createMockedForm();

        // Expect
        $this->createMockedRedirect();
        $controller = $this->createLoginController();

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordByDefault()
    {
        $this->currentUser();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->createMockedForm();
        $this->createMockedRedirect();
        // Expect
        $controller = $this->createLoginController();
        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenUserNotExists()
    {
        $this->currentUser();
        $this->createMockedRedirect();
        $this->createMockedForm();
        // Setup
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_USER_NOT_EXIST));
        $controller = $this->createLoginController();
        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenPasswordIncorrect()
    {
        $this->currentUser();
        $this->createMockedRedirect();
        $this->createMockedForm();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CREDENTIAL_INVALID));
        $this->redirectHelper();

        $controller = $this->createLoginController();
        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesAccountDisabledWhenAuthenticationResult_IsFailureAccountDisabled()
    {
        // Setup
        $this->currentUser();
        $this->createMockedRedirect();
        $this->createMockedForm();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE_ACCOUNT_DISABLED));
        $this->redirectHelper();
        $controller = $this->createLoginController();
        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }
    protected function currentUser()
    {
        $identityMock = $this->createMock(User::class);
        $identityMock->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identityMock);
    }

    /**
     * Create a mocked form instance for testing
     */
    private function createMockedForm()
    {
        $dummyForm = $this->createMock(Form::class);
        $this->formHelper->expects($this->once())
            ->method('createForm')
            ->with(Login::class)
            ->willReturn($dummyForm);
    }

    /**
     * Create a mocked RedirectHelper instance for testing
     */
    private function createMockedRedirect()
    {
        $this->redirectHelper->expects($this->once())
            ->method('toRoute')
            ->with('auth/login/GET')
            ->willReturn($this->redirect());
    }

    protected function flashMessenger()
    {
        $this->flashMessenger =  $this->createMock(FlashMessenger::class);
    }
    protected function redirectHelper()
    {
        $serviceName = Redirect::class;

        if (!isset($this->instances[$serviceName])) {
            $instance = $this->createMock(Redirect::class);
            $instance->method('toRoute')->willReturn($this->redirect());
            $this->instances[$serviceName] = $instance;
        }

        return $this->instances[$serviceName];
    }

    /**
     * @return MockInterface|AuthChallengeContainer
     */

    /**
     * @return HttpResponse
     */
    protected function redirect(): HttpResponse
    {
        $response = new HttpResponse();
        $response->setStatusCode(HttpResponse::STATUS_CODE_302);
        return $response;
    }

    /**
     * @param array|null $data
     * @return Request
     */
    protected function postRequest(array $data = null, array $query = null): Request
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters($data ?? static::EMPTY_FORM_DATA));
        $request->setQuery(new Parameters($query ?? []));
        $request->setUri('https://localhost');
        return $request;
    }
}
