<?php

namespace OlcsTest\Controller;

use Common\Controller\Plugin\HandleQuery;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\ConversationsController as Sut;
use ReflectionClass;
use LmcRbacMvc\Service\AuthorizationService;

class ConversationsControllerTest extends TestCase
{
    protected $sut;
    protected $sm;

    public function setUp(): void
    {
        $this->mockniTextTranslationUtil = m::mock(NiTextTranslation::class)->makePartial();
        $this->mockauthService = m::mock(AuthorizationService::class)->makePartial();
        $this->mockflashMessengerHelper = m::mock(FlashMessengerHelperService::class)->makePartial();
        $this->mocktableFactory = m::mock(TableFactory::class)->makePartial();

        $this->sut = m::mock(Sut::class)
                      ->makePartial()
                      ->shouldAllowMockingProtectedMethods();

        $reflectionClass = new ReflectionClass(Sut::class);
        $this->setMockedProperties($reflectionClass, 'niTextTranslationUtil', $this->mockniTextTranslationUtil);
        $this->setMockedProperties($reflectionClass, 'authService', $this->mockauthService);
        $this->setMockedProperties($reflectionClass, 'flashMessengerHelper', $this->mockflashMessengerHelper);
        $this->setMockedProperties($reflectionClass, 'tableFactory', $this->mocktableFactory);
    }

    public function setMockedProperties(ReflectionClass $reflectionClass, string $property, $value): void
    {
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->sut, $value);
    }

    public function testViewAction(): void
    {
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
                     ->andReturn(true);
        $mockResponse->shouldReceive('getResult')
                     ->andReturn([]);

        $mockHandleQuery = m::mock(HandleQuery::class)
                            ->makePartial();
        $mockHandleQuery->shouldReceive('__invoke')
                        ->andReturn($mockResponse);

        $mockParams = m::mock(Params::class);
        $mockParams->shouldReceive('fromQuery')
                   ->with('page', 1)
                   ->andReturn(1);
        $mockParams->shouldReceive('fromQuery')
                   ->with('limit', 10)
                   ->andReturn(10);
        $mockParams->shouldReceive('fromQuery')
                   ->withNoArgs()
                   ->andReturn([]);
        $mockParams->shouldReceive('fromRoute')
                   ->with('conversationId')
                   ->andReturn(1);

        $this->sut->shouldReceive('params')
                  ->andReturn($mockParams);
        $this->sut->shouldReceive('plugin')
                  ->with('handleQuery')
                  ->andReturn($mockHandleQuery);

        $table = '<table/>';

        $this->mocktableFactory->shouldReceive('buildTable')
                               ->with(
                                   'messages-view',
                                   [],
                                   ['page' => 1, 'limit' => 10, 'conversation' => 1, 'query' => []],
                               )
                               ->andReturn($table);

        $view = $this->sut->viewAction();
        $this->assertInstanceOf(ViewModel::class, $view);
        $this->assertEquals($table, $view->getVariable('table'));
    }
}