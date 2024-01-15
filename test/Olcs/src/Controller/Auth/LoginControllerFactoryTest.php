<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Auth;

use Common\Auth\Adapter\CommandAdapter;
use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Dispatcher;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\ServiceManager;
use Mockery\MockInterface;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Controller\Auth\LoginController;
use Olcs\Controller\Auth\LoginControllerFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Mockery as m;
use Olcs\Form\Model\Form\Auth\Login;

class LoginControllerFactoryTest extends MockeryTestCase
{
    //use MocksServicesTrait;

    /**
     * @var LoginControllerFactory
     */
    protected $sut;
    private $serviceManager;

    public function setUp(): void
    {
        $this->sut = m::mock(LoginControllerFactory::class)->makePartial();
        $this->serviceManager = $this->createMockedServiceManager()
;       $this->setUpDefaultServices($this->serviceManager);
    }

    /**
     * @test
     */
    public function createService_IsCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'createService']);
    }

    /**
     * @test
     * @depends createService_IsCallable
     * @depends __invoke_IsCallable
     */
    public function createService_CallsInvoke()
    {
        // Setup
        $this->sut = m::mock(LoginControllerFactory::class)->makePartial();

        // Expectations
        $this->sut->expects('__invoke')->withArgs(function ($serviceManager, $requestedName) {
            $this->assertSame($this->serviceManager, $serviceManager, 'Expected first argument to be the ServiceManager passed to createService');
            $this->assertSame(Dispatcher::class, $requestedName, 'Expected requestedName to be NULL');
            return true;
        });

        // Execute
        $this->sut->createService($this->serviceManager);
    }

    /**
     * @test
     */
    public function __invoke_IsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, '__invoke']);
    }

    /**
     * @test
     * @depends __invoke_IsCallable
     */
    public function __invoke_ReturnsAnInstanceOfDispatcherWithLoginController()
    {
        // Setup
        $this->setUpSut();
        //$this->setUpDefaultServices();
        $mockServiceManager = $this->createMockedServiceManager();

        // Execute
        $result = $this->sut->__invoke($mockServiceManager, null);

        // Assert
        $this->assertInstanceOf(Dispatcher::class, $result);
        $this->assertInstanceOf(LoginController::class, $result->getDelegate());
    }

    protected function setUpSut(): void
    {
        $this->sut = new LoginControllerFactory();
    }

    /**
     * @param ServiceManager $serviceManager
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager)
    {
        $serviceManager->setService(SelfserveCommandAdapter::class, $this->setUpMockService(SelfserveCommandAdapter::class));
        $serviceManager->setService(AuthenticationServiceInterface::class, $this->setUpMockService(AuthenticationServiceInterface::class));
        $serviceManager->setService('Auth\CookieService', $this->setUpMockService(CookieService::class));
        $serviceManager->setService(CurrentUser::class, $this->setUpMockService(CurrentUser::class));
        $serviceManager->setService(FlashMessenger::class, $this->setUpMockService(FlashMessenger::class));
        $serviceManager->setService(FormHelperService::class, $this->setUpMockService(FormHelperService::class));
        $serviceManager->setService(Redirect::class, $this->setUpMockService(Redirect::class));
        $serviceManager->setService(Url::class, $this->setUpMockService(Url::class));
    }

    private function createMockedServiceManager()
    {
        $mockedServiceManager = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set up Expectation for method calls
        $mockedServiceManager->expects($this->any())
            ->method('get')
            ->willReturnCallback([$this, 'getService']);

        return $mockedServiceManager;
    }
    public function getService($serviceName)
    {
            // Return the mocked services based on the service name
        switch ($serviceName) {
            case SelfserveCommandAdapter::class:
                return $this->setUpMockService(SelfserveCommandAdapter::class);
            case AuthenticationServiceInterface::class:
                return $this->setUpMockService(AuthenticationServiceInterface::class);
            case 'Auth\CookieService':
                return $this->setUpMockService(CookieService::class);
            case CurrentUser::class:
                return $this->setUpMockService(CurrentUser::class);
            case FlashMessenger::class:
                return $this->setUpMockService(FlashMessenger::class);
            case FormHelperService::class:
                return $this->setUpMockService(FormHelperService::class);
            case Redirect::class:
                return $this->setUpMockService(Redirect::class);
            case Url::class:
                return $this->setUpMockService(Url::class);
            case CommandAdapter::class:
                return $this->setUpMockService(CommandAdapter::class);

            default:
                return null;
        }
    }


    /**
     * @param string $class
     * @return MockInterface
     */
    protected function setUpMockService(string $class): MockInterface
    {
        $instance = m::mock($class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }
}
