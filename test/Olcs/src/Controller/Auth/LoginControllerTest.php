<?php
declare(strict_types=1);

namespace OlcsTest\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Rbac\User;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Interop\Container\Containerinterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery\MockInterface;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Controller\Auth\LoginController;
use Olcs\Form\Model\Form\Auth\Login;
use Olcs\Logging\Log\Logger;
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
    private function createLoginController(
        $authenticationAdapter = null,
        $authenticationService = null,
        $currentUser = null,
        $flashMessenger = null,
        $formHelper = null,
        $redirectHelper = null,
        $authChallengeContainer = null
    ) {
        return new LoginController(
            $authenticationAdapter ?? $this->authenticationAdapter,
            $authenticationService ?? $this->authenticationService,
            $currentUser ?? $this->currentUser,
            $flashMessenger ?? $this->flashMessenger,
            $formHelper ?? $this->formHelper,
            $redirectHelper ?? $this->redirectHelper,
            $authChallengeContainer ?? $this->authChallengeContainer
        );
    }

    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $this->setUpSut();

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
            ->willReturn($this->createMock(Redirect::class));

        // Execute
        $this->createLoginController()->indexAction();
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel()
    {
        // required Mock Dependencies
        $formHelperMock = $this->createMock(FormHelperService::class);
        $dummyForm = $this->createMock(Form::class);
        $formHelperMock->method('createForm')
            ->willReturn($dummyForm);
        $currentUserMock = $this->createMock(CurrentUser::class);
        $identityMock = $this->createMock(User::class);
        $identityMock->method('isAnonymous')->willReturn(true);
        $currentUserMock->method('getIdentity')->willReturn($identityMock);

        // Instantiate LoginController with mocked dependencies
        $loginController = $this->createLoginController(
            null,
            null,
            $currentUserMock,
            null,
            $formHelperMock,
            null,
            null
        );

        // Call indexAction
        $result = $loginController->indexAction();

        // Assertions
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithLoginForm()
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->indexAction();
        $form = $result->getVariable('form');

        // Assert
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel_WithLoginForm
     */
    public function indexAction_SetsFormData_WhenHasBeenStoredInSession()
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->flashMessenger()->allows()->hasMessages(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)->andReturn(true);
        $this->flashMessenger()->expects()->getMessages(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)->andReturn(['{"username": "username", "password":"abc"}']);

        // Execute
        $result = $this->sut->indexAction();
        $form = $result->getVariable('form');
        assert($form instanceof Form);
        $form->isValid();

        // Assert
        $expected = [
            'username' => 'username',
            'password' => 'abc',
            'submit' => null
        ];
        $this->assertEquals($expected, $result->getVariable('form')->getData());
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexAction_ReturnsViewModel_WithFailureReason_WhenAuthenticationFails()
    {
        // Setup
        $this->setUpSut();

        $flashMessenger = $this->serviceManager->get(FlashMessenger::class);
        assert($flashMessenger instanceof MockInterface);
        $flashMessenger->shouldReceive('hasMessages')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)
            ->andReturnTrue();
        $flashMessenger->shouldReceive('getMessagesFromNamespace')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)
            ->andReturn(['failureReason']);

        // Execute
        $result = $this->sut->indexAction();

        // Assert
        $this->assertArrayHasKey('failureReason', $result->getVariables());
    }

    /**
     * @test
     */
    public function postAction_IsCallable()
    {
        // Setup

        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'postAction']);
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    protected function postAction_RedirectsToDashboard_WhenUserAlreadyLoggedIn()
    {
        // Setup
        $this->setUpSut();
        $this->currentUser()->allows('getIdentity')->andReturn($this->identity(false));

        // Expect
        $this->redirectHelper()->expects('toRoute')->with(LoginController::ROUTE_INDEX)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($this->postRequest(), new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FlashesFormData_WhenFormInvalid()
    {
        // Setup
        $this->setUpSut();

        $this->redirectHelper()->allows('toRoute')->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs(function ($message, $namespace) {
            $this->assertSame(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT, $namespace);
            return true;
        });

        // Execute
        $this->sut->postAction($this->postRequest(), new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_SuccessfulAuth_RedirectsToDashBoard_WhenGotoNotPresent()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password']);
        $response = new Response();

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->redirectHelper()->expects()->toRoute(LoginController::ROUTE_INDEX)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     */
    public function postAction_SuccessfulAuth_RedirectsToGoto_WhenPresentAndValid()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password'],
            ['goto' => 'https://localhost/goto/url']
        );
        $response = new Response();

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->redirectHelper()->expects()->toUrl('https://localhost/goto/url')->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     */
    public function postAction_SuccessfulOAuth_RedirectsToDashboard_WhenGotoPresentAndInvalid()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password'],
            ['goto' => 'https://example.com/goto/url']
        );
        $response = new Response();

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->redirectHelper()->expects()->toRoute(LoginController::ROUTE_INDEX)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_NewPasswordRequiredChallenge_StoresChallengeInSession()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));

        $this->redirectHelper()
            ->allows()
            ->toRoute(
                LoginController::ROUTE_AUTH_EXPIRED_PASSWORD,
                ['authId' => 'authId']
            )->andReturn($this->redirect());

        // Expect
        $this->authChallengeContainer()->expects('setChallengeName')->andReturnSelf();
        $this->authChallengeContainer()->expects('setChallengeSession')->andReturnSelf();
        $this->authChallengeContainer()->expects('setChallengedIdentity')->andReturnSelf();

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_NewPasswordRequiredChallenge_StoresChallengeInSession
     */
    public function postAction_NewPasswordRequiredChallenge_RedirectsToExpiredPassword()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));

        $this->authChallengeContainer()->allows('setChallengeName')->andReturnSelf();
        $this->authChallengeContainer()->allows('setChallengeSession')->andReturnSelf();
        $this->authChallengeContainer()->allows('setChallengedIdentity')->andReturnSelf();

        // Expect
        $this->redirectHelper()->expects()->toRoute(LoginController::ROUTE_AUTH_EXPIRED_PASSWORD, ['USER_ID_FOR_SRP' => 'username'])->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_UnsupportedChallenge_RedirectsToLoginPage()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_UNSUPPORTED));

        // Expect
        $this->redirectHelper()->expects()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_RedirectsToLoginPage()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->flashMessenger()->allows('addMessage')->withArgs(['failed', LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR]);

        // Expect
        $this->redirectHelper()->expects()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordByDefault()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->redirectHelper()->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs([LoginController::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD, LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR]);

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenUserNotExists()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_USER_NOT_EXIST));
        $this->redirectHelper()->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs([LoginController::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD, LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR]);

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenPasswordIncorrect()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CREDENTIAL_INVALID));
        $this->redirectHelper()->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs([LoginController::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD, LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR]);

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesAccountDisabledWhenAuthenticationResult_IsFailureAccountDisabled()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationService()->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE_ACCOUNT_DISABLED));
        $this->redirectHelper()->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs([LoginController::TRANSLATION_KEY_SUFFIX_AUTH_ACCOUNT_DISABLED, LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR]);

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function setUpSut()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->sut = new LoginController(
            $this->createMock(ValidatableAdapterInterface::class),
            $this->createMock(AuthenticationServiceInterface::class),
            $this->createMock(CurrentUser::class),
            $this->createMock(FlashMessenger::class),
            $this->createMock(FormHelperService::class),
            $this->createMock(Redirect::class),
            $this->createMock(AuthChallengeContainer::class)
        );
        // Inject the container into the controller
        $this->setContainer($this->container);
    }

    /**
     * @param ServiceManager $serviceManager
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager)
    {
        $this->authenticationAdapter();
        $this->authenticationService();
        $this->currentUser();
        $this->flashMessenger();
        $this->formHelper();
        $this->redirectHelper();
        $this->authChallengeContainer();
    }

    /**
     * @return MockInterface|AuthenticationServiceInterface
     */
    protected function authenticationService()
    {
        if (!$this->serviceManager->has(AuthenticationServiceInterface::class)) {
            $instance = $this->setUpMockService(AuthenticationServiceInterface::class);
            $this->serviceManager->setService(AuthenticationServiceInterface::class, $instance);
        }
        $instance = $this->serviceManager->get(AuthenticationServiceInterface::class);
        return $instance;
    }

    /**
     * @return MockInterface|SelfserveCommandAdapter
     */
    protected function authenticationAdapter()
    {
        if (!$this->serviceManager->has(SelfserveCommandAdapter::class)) {
            $instance = $this->setUpMockService(SelfserveCommandAdapter::class);
            $this->serviceManager->setService(SelfserveCommandAdapter::class, $instance);
        }
        $instance = $this->serviceManager->get(SelfserveCommandAdapter::class);
        return $instance;
    }


    protected function currentUser()
    {
        $instance = $this->createMock(CurrentUser::class);
        $instance->expects($this->once())
            ->method('getIdentity')
            ->willReturn($this->createMock(CurrentUser::class));

        return $instance;
    }

    protected function identity(bool $isAnonymous = true)
    {
        $identity = $this->createMock(User::class);
        $identity->expects($this->once())
        ->method('isAnonymous')->willReturn($isAnonymous);
        return $identity;
    }

    /**
     * @return MockInterface|FormHelperService
     */
    protected function formHelper()
    {
        if (!$this->serviceManager->has(FormHelperService::class)) {
            $instance = $this->setUpMockService(FormHelperService::class);
            $instance->allows('createForm')->andReturnUsing(function () {
                $formBuilder = new AnnotationBuilder();
                return $formBuilder->createForm(Login::class);
            })->byDefault();
            $this->serviceManager->setService(FormHelperService::class, $instance);
        }
        $instance = $this->serviceManager->get(FormHelperService::class);
        return $instance;
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
    private function authChallengeContainer()
    {
        if (!$this->serviceManager->has(AuthChallengeContainer::class)) {
            $instance = $this->setUpMockService(AuthChallengeContainer::class);
            $this->serviceManager->setService(AuthChallengeContainer::class, $instance);
        }
        $instance = $this->serviceManager->get(AuthChallengeContainer::class);
        assert($instance instanceof MockInterface);
        return $instance;
    }

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

    private static function setupLogger()
    {
        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);

        Logger::setLogger($logger);
    }
}
