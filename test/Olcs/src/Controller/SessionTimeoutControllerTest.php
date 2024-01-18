<?php
declare(strict_types = 1);

namespace OlcsTest\Controller;

use Common\Controller\Plugin\Redirect;
use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\User;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\MockInterface;
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

    private $identityProviderClass = JWTIdentityProvider::class;

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
     * @depends indexAction_IsCallable
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
     * @depends indexAction_ReturnsViewModelIfIdentityIsAnonymous
     */
    public function indexAction_ReturnsViewModelIfIdentityIsNull()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator, new Request());

        // Define Expectations
        $currentUser = $this->resolveMockService($serviceLocator, IdentityProviderInterface::class);
        $currentUser->shouldReceive('getIdentity')->withNoArgs()->andReturnNull()->once();

        // Execute
        $result = $sut->indexAction($this->setUpRequest());

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }


    /**
     * @test
     * @depends indexAction_ReturnsViewModelIfIdentityIsNull
     */
    public function indexAction_LogsOutUserIfLoggedIn()
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $request = $this->setUpRequest();
        $sut = $this->setUpSut($serviceLocator, new Request());

        $this->setUpIdentityWithClearSession($this->identityProviderClass);

        // Execute
        $response = $sut->indexAction($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     * @depends indexAction_LogsOutUserIfLoggedIn
     * @dataProvider dpIdentityProviderClass
     */
    public function indexAction_RedirectsUserIfLoggedIn(string $identityProviderClass)
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $request = $this->setUpRequest();
        $sut = $this->setUpSut($serviceLocator, new Request());

        $this->setUpIdentityWithClearSession($identityProviderClass);

        // Define Expectations
        $redirectHelper = $this->resolveMockService($serviceLocator, Redirect::class);
        $redirectHelper->shouldReceive('refresh')
            ->withNoArgs()
            ->andReturn($expectedResponse = new Response())
            ->once();

        // Execute
        $response = $sut->indexAction($request);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    public function dpIdentityProviderClass(): array
    {
        return [
            [$this->identityProviderClass],
            [JWTIdentityProvider::class],
        ];
    }

    /**
     * "
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function setUpDefaultServices(ServiceLocatorInterface $serviceLocator): array
    {
        return [
            IdentityProviderInterface::class => $this->setUpIdentity($this->identityProviderClass),
            Redirect::class => $this->setUpRedirect(),
            'request' => $this->setUpMockService(Request::class),
        ];
    }

    /**
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return MvcEvent
     */
    protected function setUpMvcEvent(Request $request, RouteMatch $routeMatch): MvcEvent
    {
        $event = new MvcEvent();
        $event->setRequest($request);
        $event->setRouteMatch($routeMatch);
        $router = $this->setUpMockService(TreeRouteStack::class);
        $event->setRouter($router);
        return $event;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return PluginManager
     */
    protected function setUpPluginManager(Containerinterface $serviceLocator): PluginManager
    {
        $pluginManager = new PluginManager($serviceLocator);
        return $pluginManager;
    }

    /**
     * @return SessionTimeoutController
     */
    protected function setUpSut()
    {
        // Mock Dependencies
        $identityProvide = $this->createMock(IdentityProviderInterface::class);
        $redirectHelper = $this->createMock(Redirect::class);
        $cookieService = $this->createMock(CookieService::class);
        $logoutService = $this->createMock(LogoutService::class);

        //Mock Event
        $event = $this->createMock(MvcEvent::class);
        $pluginManager = $this->createMock(PluginManager::class);

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
     * @param string $identityProvider
     * @return m\MockInterface
     */
    protected function setUpIdentity(string $identityProvider): m\MockInterface
    {
        $identity = $this->setUpMockService(User::class);
        $identity->shouldReceive('isAnonymous')
            ->andReturnFalse()
        ->byDefault();

        $currentUser =  $this->getMockServiceWithName($identityProvider, IdentityProviderInterface::class);
        $currentUser->shouldReceive('getIdentity')
            ->withNoArgs()
            ->andReturn($identity)
            ->byDefault();
        $currentUser->expects('clearSession')
            ->never()
            ->byDefault();

        return $currentUser;
    }

    protected function getMockServiceWithName(string $class, string $serviceName): MockInterface
    {
        if (!$this->serviceManager->has($serviceName)) {
            $this->serviceManager->setService(
                $serviceName,
                $this->setUpMockService($class)
            );
        }

        return $this->serviceManager->get($serviceName);
    }

    protected function setUpIdentityWithClearSession(string $identityProvider): void
    {
        $identity = $this->setUpMockService(User::class);
        $identity->expects('isAnonymous')
            ->withNoArgs()
            ->andReturnFalse();

        $currentUser =  $this->getMockServiceWithName($identityProvider, IdentityProviderInterface::class);
        $currentUser->expects('getIdentity')
            ->withNoArgs()
            ->andReturn($identity);
        $currentUser->expects('clearSession')
            ->withNoArgs();
    }

    /**
     * @return m\MockInterface
     */
    protected function setUpRedirect(): m\MockInterface
    {
        $redirect = $this->setUpMockService(Redirect::class);
        $redirect->shouldReceive('refresh')
            ->withNoArgs()
            ->andReturn(new Response())
            ->byDefault();

        return $redirect;
    }
}
