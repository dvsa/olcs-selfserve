<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Controller\Plugin\Redirect;
use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\User;
use Interop\Container\Containerinterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Olcs\Controller\SessionTimeoutController;
use Olcs\Controller\SessionTimeoutControllerFactory;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use PHPUnit\Framework\TestCase;

/**
 * @see SessionTimeoutController
 */
class SessionTimeoutControllerTest extends TestCase
{
    protected const COOKIE_NAME = 'cookie';
    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        $sut = $this->setUpSut();

        // Assert
        $this->assertTrue(method_exists($sut, 'indexAction') && is_callable([$sut, 'indexAction']));
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModelIfIdentityIsAnonymous()
    {
        // Arrange
        $container = $this->createMock(ContainerInterface::class);

        // Mock dependencies
        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $redirectHelper = $this->createMock(Redirect::class);
        $cookieService = $this->createMock(CookieService::class);
        $logoutService = $this->createMock(LogoutService::class);

        // Setup container to return mocked dependencies
        $container->method('get')->willReturnMap([
            [IdentityProviderInterface::class, $identityProvider],
            [Redirect::class, $redirectHelper],
            ['Auth\CookieService', $cookieService],
            ['Auth\LogoutService', $logoutService],
            ['ControllerPluginManager', $container]
        ]);

        // Create controller instance using factory and set container
        $factory = new SessionTimeoutControllerFactory();
        $controller = $factory($container, SessionTimeoutController::class);

        // Create a mock request (you may adjust this based on your requirements)
        $request = new Request();
        $uri = $this->createMock(Http::class);
        $uri->method('toString')->willReturn('http://example.com');
        $request->setUri($uri);
        $request->setQuery(new Parameters([]));

        // Create a mock MvcEvent and set it on the controller
        $routeMatch = new RouteMatch([]);
        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest($request);
        $mvcEvent->setRouteMatch($routeMatch);
        $controller->setEvent($mvcEvent);

        // Act
        $result = $controller->indexAction();

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModelIfIdentityIsNull()
    {
        // Mock the IdentityProviderInterface
        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $identityProvider->expects($this->once())
            ->method('getIdentity')
            ->willReturn(null);

        // Mock dependencies
        $redirectHelper = $this->createMock(Redirect::class);

        // Instantiate the controller manually, injecting the mocked dependencies
        $controller = new SessionTimeoutController($identityProvider, $redirectHelper);

        // Execute
        $result = $controller->indexAction($this->setUpRequest());
        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }


    /**
     * @test
     */
    public function indexAction_LogsOutUserIfLoggedIn()
    {
        // Mock JWTIdentityProvider
        $identityProvider = $this->createMock(JWTIdentityProvider::class);
        // Assume the identity is not anonymous (logged in)
        $identityProvider->expects($this->once())
            ->method('getIdentity')
            ->willReturn($this->createMock(User::class));
        // Mock the clearSession method
        $identityProvider->expects($this->once())
            ->method('clearSession');

        // Assume refresh() returns a Response object
        $redirectHelper = $this->setUpRedirect();

        // Instantiate the controller manually, injecting the mocked dependencies
        $controller = new SessionTimeoutController($identityProvider, $redirectHelper);

        // Execute
        $result = $controller->indexAction($this->createMock(Request::class));

        // Assert
        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_RedirectsUserIfLoggedIn()
    {
        // Mock JWTIdentityProvider
        $identityProvider = $this->createMock(JWTIdentityProvider::class);
        // Assume the identity is not anonymous (logged in)
        $identityProvider->expects($this->once())
        ->method('getIdentity')
        ->willReturn($this->createMock(User::class));

        // Mock Redirect plugin
        $redirectHelper = $this->setUpRedirect();

        // Instantiate the controller manually, injecting the mocked dependencies
        $controller = new SessionTimeoutController($identityProvider, $redirectHelper);

        // Execute
        $response = $controller->indexAction($this->createMock(Request::class));

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @return SessionTimeoutController
     */
    protected function setUpSut()
    {
        // Mock Dependencies
        $identityProvide = $this->createMock(IdentityProviderInterface::class);
        $redirectHelper = $this->createMock(Redirect::class);

        return new SessionTimeoutController(
            $identityProvide,
            $redirectHelper
        );
    }

    /**
     * @param string $url
     * @param array|null $input
     * @return Request
     */
    protected function setUpRequest(?string $url = null, array $input = null)
    {
        $uri = m::mock(Http::class);
        $uri->shouldIgnoreMissing($uri);
        $uri->shouldReceive('toString')->andReturn($url ?? 'foobarbaz');

        $request = new Request();
        $request->setUri($uri);
        $request->setQuery(new Parameters($input ?? []));

        return $request;
    }

    /**
     * @return Redirect
     */
    protected function setUpRedirect(): Redirect
    {
        // Mock Redirect plugin
        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())
            ->method('refresh')
            ->willReturn(new Response());

        return $redirect;
    }
}
