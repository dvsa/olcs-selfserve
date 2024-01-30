<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Controller\Plugin\Redirect;
use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\User;
use Interop\Container\Containerinterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Olcs\Controller\SessionTimeoutController;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @see SessionTimeoutController
 */
class SessionTimeoutControllerTest extends TestCase
{
    protected const COOKIE_NAME = 'cookie';
    private $identityProviderClass = JWTIdentityProvider::class;
    private IdentityProviderInterface $identityProviderMock;
    protected Redirect $redirectHelperMock;
    protected SessionTimeoutController  $sut;

    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Assert
        $this->assertTrue(method_exists($this->sut, 'indexAction') && is_callable([$this->sut, 'indexAction']));
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModelIfIdentityIsAnonymous()
    {
        // Define Expectations
        $identity = m::mock(User::class);
        $identity->shouldReceive('isAnonymous')->andReturnTrue();
        $this->identityProviderMock->shouldReceive('getIdentity')->withNoArgs()->andReturn($identity);

        // Execute
        $result = $this->sut->indexAction($this->setUpRequest());

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_ReturnsViewModelIfIdentityIsNull()
    {
        // Define Expectations
        $this->identityProviderMock->shouldReceive('getIdentity')->withNoArgs()->andReturnNull()->once();

        // Execute
        $result = $this->sut->indexAction($this->setUpRequest());

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexAction_LogsOutUserIfLoggedIn()
    {
        //setup
        $request = $this->setUpRequest();

        //Define Expectation
        $this->redirectHelperMock->shouldReceive('refresh')
            ->withNoArgs()
            ->andReturn($expectedResponse = new Response())
            ->once();

        $this->setUpIdentityWithClearSession();

        // Execute
        $response = $this->sut->indexAction($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     * @dataProvider dpIdentityProviderClass
     */
    public function indexAction_RedirectsUserIfLoggedIn(string $identityProviderClass)
    {
        // Setup
        $request = $this->setUpRequest();

        $this->setUpIdentityWithClearSession();

        // Define Expectations
        $this->redirectHelperMock->shouldReceive('refresh')
            ->withNoArgs()
            ->andReturn($expectedResponse = new Response())
            ->once();

        // Execute
        $response = $this->sut->indexAction($request);

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

    protected function setup(): void
    {
        $this->identityProviderMock = m::mock(IdentityProviderInterface::class);
        $this->redirectHelperMock = m::mock(Redirect::class);

        $this->sut = new SessionTimeoutController(
            $this->identityProviderMock,
            $this->redirectHelperMock
        );
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param Request $request
     * @param RouteMatch|null $routeMatch
     */
    protected function setUpSut()
    {
        $container = m::mock(ContainerInterface::class);
        $controllerPluginManagerMock = m::mock(PluginManager::class);
        // Set expectations for the container's `get` method
        $controllerPluginManagerMock->shouldReceive('get')->withArgs([
            'ControllerPluginManager',
        ])->andReturn($controllerPluginManagerMock);

        $container->shouldReceive('get')->withArgs([
            IdentityProviderInterface::class,
        ])->andReturn($this->identityProviderMock);

        $controllerPluginManagerMock->shouldReceive('get')->withArgs([
            Redirect::class,
        ])->andReturn($controllerPluginManagerMock);
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

    protected function setUpIdentityWithClearSession(): void
    {
        $identity = m::mock(User::class);
        $identity->expects('isAnonymous')
            ->withNoArgs()
            ->andReturnFalse();

        $this->identityProviderMock->expects('getIdentity')
            ->withNoArgs()
            ->andReturn($identity);
        $this->identityProviderMock->expects('clearSession')
            ->withNoArgs();
    }
}
