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
use Laminas\Authentication\Result;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Router\Http\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Controller\Auth\LoginController;
use Olcs\Form\Model\Form\Auth\Login;
use Olcs\Logging\Log\Logger;

class LoginControllerTest extends MockeryTestCase
{
    /**
     * @var SelfserveCommandAdapter
     */
    private $authenticationAdapterMock;

    /** @var AuthenticationServiceInterface */
    protected $authenticationServiceMock;

    /**
     * @var CurrentUser
     */
    private $currentUserMock;

    /**
     * @var FlashMessenger
     */
    private $flashMessengerMock;

    /**
     * @var FormHelperService
     */
    protected $formHelperMock;

    /**
     * @var Redirect
     */
    protected $redirectHelperMock;
    /**
     * @var AuthChallengeContainer
     */
    protected $authChallengeContainerMock;

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

    /**
     * @var LoginController
     */
    protected $sut;

    protected function setUp(): void
    {
        $this->authenticationAdapterMock = m::mock(SelfserveCommandAdapter::class);
        $this->authenticationServiceMock = m::mock(AuthenticationServiceInterface::class);
        $this->currentUserMock = m::mock(CurrentUser::class);
        $this->flashMessengerMock = m::mock(FlashMessenger::class);
        $this->formHelperMock = m::mock(FormHelperService::class);
        $this->redirectHelperMock = m::mock(Redirect::class);
        $this->authChallengeContainerMock = m::mock(AuthChallengeContainer::class);
        self::setupLogger();
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
        // Setup
        $this->setUpSut();
        $this->currentUserMock->allows('getIdentity')->andReturn($this->identity(false));

        // Expect
        $this->redirectHelperMock->expects('toRoute')->with(LoginController::ROUTE_INDEX)->andReturn($this->redirect());

        // Execute
        $this->sut->indexAction();
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel()
    {
        // Setup
        $this->setUpSut();
        // Expect
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        // Execute
        $result = $this->sut->indexAction();

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModel_WithLoginForm()
    {
        // Setup
        $this->setUpSut();
        $this->flashMessengerMock->shouldReceive('hasMessages')->withAnyArgs();
        $this->flashMessengerMock->shouldReceive('getMessages')->withAnyArgs();
        // Execute
        $result = $this->sut->indexAction();
        $form = $result->getVariable('form');

        // Assert
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * @test
     */
    public function indexAction_SetsFormData_WhenHasBeenStoredInSession()
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->flashMessengerMock->allows()->hasMessages(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)->andReturn(true);
        $this->flashMessengerMock->allows()->hasMessages(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)->andReturn(false); // Return false for this namespace
        $this->flashMessengerMock->expects()->getMessages(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)->andReturn(['{"username": "username", "password":"abc"}']);

        $this->flashMessengerMock->shouldReceive('getMessagesFromNamespace')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)
            ->andReturnTrue();

        // Execute
        $result = $this->sut->indexAction();
        $form = $result->getVariable('form');
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
     */
    public function indexAction_ReturnsViewModel_WithFailureReason_WhenAuthenticationFails()
    {
        // Setup
        $this->setUpSut();

        $this->flashMessengerMock->allows()->hasMessages(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)->andReturn(false);
        $this->flashMessengerMock->allows()->hasMessages(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)->andReturn(true);
        $this->flashMessengerMock
            ->allows('getMessages')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            ->andReturn(['{"username": "username", "password":"abc"}']);

        $this->flashMessengerMock->shouldReceive('getMessagesFromNamespace')
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
     */
    protected function postAction_RedirectsToDashboard_WhenUserAlreadyLoggedIn()
    {
        // Setup
        $this->setUpSut();
        $this->currentUserMock->allows('getIdentity')->andReturn($this->identity(false));

        // Expect
        $this->redirectHelperMock->expects('toRoute')->with(LoginController::ROUTE_INDEX)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($this->postRequest(), new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FlashesFormData_WhenFormInvalid()
    {
        // Setup
        $this->setUpSut();

        $this->redirectHelperMock->allows('toRoute')->andReturn($this->redirect());

        // Expect
        $this->flashMessengerMock->expects('addMessage')->withArgs(function ($message, $namespace) {
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
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();
        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->redirectHelperMock->expects()->toRoute(LoginController::ROUTE_INDEX)->andReturn($this->redirect());

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

        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->redirectHelperMock->expects()->toUrl('https://localhost/goto/url')->andReturn($this->redirect());

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
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL));

        // Expect
        $this->redirectHelperMock->expects()->toRoute(LoginController::ROUTE_INDEX)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     */
    public function postAction_NewPasswordRequiredChallenge_StoresChallengeInSession()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));

        $this->redirectHelperMock
            ->allows()
            ->toRoute(
                LoginController::ROUTE_AUTH_EXPIRED_PASSWORD,
                ['authId' => 'authId']
            )->andReturn($this->redirect());

        // Expect
        $this->authChallengeContainerMock->expects('setChallengeName')->andReturnSelf();
        $this->authChallengeContainerMock->expects('setChallengeSession')->andReturnSelf();
        $this->authChallengeContainerMock->expects('setChallengedIdentity')->andReturnSelf();

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_NewPasswordRequiredChallenge_RedirectsToExpiredPassword()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));

        $this->authChallengeContainerMock->allows('setChallengeName')->andReturnSelf();
        $this->authChallengeContainerMock->allows('setChallengeSession')->andReturnSelf();
        $this->authChallengeContainerMock->allows('setChallengedIdentity')->andReturnSelf();
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        // Expect
        $this->redirectHelperMock->expects()->toRoute(LoginController::ROUTE_AUTH_EXPIRED_PASSWORD, ['USER_ID_FOR_SRP' => 'username'])->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_UnsupportedChallenge_RedirectsToLoginPage()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_UNSUPPORTED));

        // Expect
        $this->redirectHelperMock->expects()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FailedAuthentication_RedirectsToLoginPage()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->flashMessengerMock->allows('addMessage')->withAnyArgs();

        // Expect
        $this->redirectHelperMock->expects()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordByDefault()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->redirectHelperMock->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessengerMock->expects('addMessage')
            ->times(2)
            ->withAnyArgs();

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenUserNotExists()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_USER_NOT_EXIST));
        $this->redirectHelperMock->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessengerMock->expects('addMessage')->times(2)->withAnyArgs();

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenPasswordIncorrect()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_CREDENTIAL_INVALID));
        $this->redirectHelperMock->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessengerMock->expects('addMessage')->times(2)->withAnyArgs();

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     */
    public function postAction_FailedAuthentication_FlashesAccountDisabledWhenAuthenticationResult_IsFailureAccountDisabled()
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password']
        );
        $this->authenticationAdapterMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setIdentity')->andReturn($this->identity())->byDefault();
        $this->authenticationAdapterMock->allows('setCredential')->andReturn($this->identity())->byDefault();

        $this->authenticationServiceMock->allows('authenticate')->andReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE_ACCOUNT_DISABLED));
        $this->redirectHelperMock->allows()->toRoute(LoginController::ROUTE_AUTH_LOGIN_GET)->andReturn($this->redirect());

        // Expect
        $this->flashMessengerMock->expects('addMessage')->times(2)->withAnyArgs();

        // Execute
        $this->sut->postAction($request, new RouteMatch([]), new Response());
    }

    protected function setUpSut()
    {
        $this->setUpDefaultServices();
        $this->sut = new LoginController(
            $this->authenticationAdapterMock,
            $this->authenticationServiceMock,
            $this->currentUserMock,
            $this->flashMessengerMock,
            $this->formHelperMock,
            $this->redirectHelperMock,
            $this->authChallengeContainerMock
        );
    }

    protected function setUpDefaultServices()
    {
        $this->currentUser();
        $this->formHelper();
        $this->redirectHelper();
    }

    protected function currentUser()
    {
        $this->currentUserMock->allows('getIdentity')->andReturn($this->identity())->byDefault();
    }

    protected function identity(bool $isAnonymous = true)
    {
        $identity = m::mock(User::class);
        $identity->shouldReceive('isAnonymous')->andReturn($isAnonymous);
        return $identity;
    }

    protected function formHelper()
    {
        $this->formHelperMock->allows('createForm')->andReturnUsing(function () {
            $formBuilder = new AnnotationBuilder();
            return $formBuilder->createForm(Login::class);
        })->byDefault();
    }

    protected function redirectHelper()
    {
        $this->redirectHelperMock->allows('toRoute')->andReturn($this->redirect())->byDefault();
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
