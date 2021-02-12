<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Service\Cqrs\Response as QueryResponse;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Common\Test\Builder\ServiceManagerBuilder;
use Common\Test\Builder\TableBuilderMockBuilder;
use Common\Test\Builder\TableFactoryMockBuilder;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\IsAnything;
use Hamcrest\Core\IsIdentical;
use Hamcrest\Arrays\IsArrayContainingKey;
use Hamcrest\Core\IsInstanceOf;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Mockery\MockInterface;
use Olcs\Controller\Licence\Vehicle\ListVehicleController;
use Olcs\Table\TableEnum;

class ListVehicleControllerTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function indexAction_IsCallable()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);

        // Assert
        $this->assertIsCallable([$sut, 'indexAction']);
    }

    /**
     * @depends indexAction_IsCallable
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WhenNoFormatIsProvided()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @depends indexAction_IsCallable
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['format' => ListVehicleController::FORMAT_HTML]));
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithLicence()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);

        $queryHandler = $serviceManager->get(HandleQuery::class);
        assert($queryHandler instanceof MockInterface, 'Expected instance of MockInterface');
        $licence = ['id' => 1, 'licNo' => 'foo'];
        $licenceQueryMatcher = IsInstanceOf::anInstanceOf(Licence::class);
        $licenceQueryResponse = m::mock(QueryResponse::class);
        $licenceQueryResponse->shouldIgnoreMissing();
        $licenceQueryResponse->shouldReceive('getResult')->andReturn($licence);
        $queryHandler->shouldReceive('__invoke')->with($licenceQueryMatcher)->andReturn($licenceQueryResponse);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertSame($licence, $result->getVariables()['licence'] ?? null);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithExportCurrentAndRemovedCsvAction()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertArrayHasKey('exportCurrentAndRemovedCsvAction', $result->getVariables());
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithExportCurrentAndRemovedCsvAction_WithFormatQueryParameter()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $urlHelper = $serviceManager->get(Url::class);
        assert($urlHelper instanceof MockInterface, 'Expected instance of MockInterface');
        $expectedUrl = "abcdefg";

        // Define Expectations
        $queryMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('format', 'csv');
        $optionsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $urlHelper->shouldReceive('fromRoute')->with('licence/vehicle/list/GET', $routeParams, $optionsMatcher)->andReturn($expectedUrl);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertEquals($expectedUrl, $result->getVariables()['exportCurrentAndRemovedCsvAction']);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithExportCurrentAndRemovedCsvAction_WithIncludeRemovedQueryParameter()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $licenceId = 1;
        $routeMatch = new RouteMatch($routeParams = ['licence' => $licenceId]);
        $urlHelper = $serviceManager->get(Url::class);
        assert($urlHelper instanceof MockInterface, 'Expected instance of MockInterface');
        $expectedUrl = "abcdefg";

        // Define Expectations
        $queryMatcher = IsArrayContainingKey::hasKeyInArray('includeRemoved');
        $optionsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $urlHelper->shouldReceive('fromRoute')->with('licence/vehicle/list/GET', $routeParams, $optionsMatcher)->andReturn($expectedUrl);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertEquals($expectedUrl, $result->getVariables()['exportCurrentAndRemovedCsvAction']);
    }

    /**
     * @test
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     */
    public function indexAction_RespondsInHtmlFormat_AndConfiguresCurrentVehicleTable_Query()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);

        $query = [
            ListVehicleController::QUERY_KEY_SORT_CURRENT_VEHICLES_TABLE => 'foo',
            ListVehicleController::QUERY_KEY_ORDER_CURRENT_VEHICLES_TABLE => 'bar',
            'limit' => 56,
        ];
        $request = new Request();
        $request->setQuery(new Parameters($query));
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $queryMatcher = IsIdentical::identicalTo($query);
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('query', $queryMatcher);
        $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_AndConfiguresCurrentVehicleTable_Page_WhenNoPageIsSetOnARequest()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_AndConfiguresCurrentVehicleTable_Page_WhenEmptyPageIsSetOnARequest()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['page' => '']));
        $routeMatch = new RouteMatch([]);

        // Define Expectations
        $paramsMatcher = IsArrayContainingKeyValuePair::hasKeyValuePair('page', 1);
        $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_CURRENT, null, $paramsMatcher);

        // Execute
        $sut->indexAction($request, $routeMatch);
    }

    public function setUpRemovedTableTitleData()
    {
        return [
            'title when table total not equal to one' => [2, 'licence.vehicle.list.section.removed.header.title.plural'],
            'title when table total is one' => [1, 'licence.vehicle.list.section.removed.header.title.singular'],
        ];
    }

    /**
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     * @dataProvider setUpRemovedTableTitleData
     * @test
     */
    public function indexAction_RespondsInHtmlFormat_WithPluralRemovedVehicleTableTitle(int $total, string $expectedTranslationKey)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $expectedTitle = 'foo';
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn($total);
        $tableBuilder->shouldReceive('getLimit')->andReturn($total);
        $translator = $this->resolveTranslator($serviceManager);

        // Define Expectations
        $translator->shouldReceive('translateReplace')->once()->with($expectedTranslationKey, [$total])->andReturn($expectedTitle);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);
        $title = $result->getVariable('removedVehicleTableTitle');

        // Assert
        $this->assertEquals($expectedTitle, $title);
    }

    /**
     * @test
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     */
    public function indexAction_SetShowRemovedVehiclesToFalse_WhenALicenceHasNoRemovedVehicles()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn(0);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $matcher = IsArrayContainingKeyValuePair::hasKeyValuePair('showRemovedVehicles', false);
        $this->assertTrue($matcher->matches((array) $result->getVariables()), 'Expected result variables to have a key "showRemovedVehicles" with a value of false');
    }

    /**
     * @test
     * @depends indexAction_RespondsInHtmlFormat_WhenHtmlFormatIsProvided
     */
    public function indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn(1);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $matcher = IsArrayContainingKeyValuePair::hasKeyValuePair('showRemovedVehicles', true);
        $this->assertTrue($matcher->matches((array) $result->getVariables()), 'Expected result variables to have a key "showRemovedVehicles" with a value of true');
    }

    /**
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     */
    public function indexAction_SetsExpandRemovedVehicles_WhenQueryParamIsSet_AndALicenceHasOneRemovedVehicle()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn(1);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertTrue($result->getVariable('showRemovedVehiclesExpanded'));
    }

    /**
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     */
    public function indexAction_DoesNotSetExpandRemovedVehicles_WhenQueryParamIsSet_AndThereAreNoRemovedVehicles()
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn(0);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $this->assertArrayNotHasKey('showRemovedVehiclesExpanded', $result->getVariables());
    }

    /**
     * @return array
     */
    public function buttonTranslationKeyTypes(): array
    {
        return [
            'title' => ['title'],
            'label' => ['label'],
        ];
    }

    /**
     * @param string $type
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     * @dataProvider buttonTranslationKeyTypes
     */
    public function indexAction_SetToggleRemovedVehiclesActionTitle_WithRelevantMessage_WhenQueryParamIsSet_AndLicenceHasRemovedVehicles(string $type)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn(1);
        $expectedKey = sprintf('toggleRemovedVehiclesAction%s', ucfirst($type));
        $expectedTitle = sprintf('licence.vehicle.list.section.removed.action.hide-removed-vehicles.%s', $type);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $variables = (array) $result->getVariables();
        $this->assertArrayHasKey($expectedKey, $variables, sprintf('Expected result variables to have a key "%s"', $expectedKey));
        $this->assertEquals($expectedTitle, $variables[$expectedKey], sprintf('Expected result variable "%s" to have a value of "%s"', $expectedKey, $expectedTitle));
    }

    /**
     * @param string $type
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     * @dataProvider buttonTranslationKeyTypes
     */
    public function indexAction_SetToggleRemovedVehiclesButtonTitle_WithRelevantMessage_WhenQueryParamIsNotSet_AndLicenceDoesNotHaveRemovedVehicles(string $type)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn(0);
        $expectedKey = sprintf('toggleRemovedVehiclesAction%s', ucfirst($type));
        $expectedTitle = sprintf('licence.vehicle.list.section.removed.action.show-removed-vehicles.%s', $type);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $variables = (array) $result->getVariables();
        $this->assertArrayHasKey($expectedKey, $variables, sprintf('Expected result variables to have a key "%s"', $expectedKey));
        $this->assertEquals($expectedTitle, $variables[$expectedKey], sprintf('Expected result variable "%s" to have a value of "%s"', $expectedKey, $expectedTitle));
    }

    /**
     * @param string $type
     * @test
     * @depends indexAction_SetShowRemovedVehiclesToTrue_WhenALicenceHasOneRemovedVehicle
     * @dataProvider buttonTranslationKeyTypes
     */
    public function indexAction_SetToggleRemovedVehiclesButtonTitle_WithRelevantMessage_WhenQueryParamIsSet_AndLicenceHasRemovedVehicles(string $type)
    {
        // Setup
        $serviceManager = $this->setUpServiceManager();
        $sut = $this->setUpSut($serviceManager);
        $request = new Request();
        $request->setQuery(new Parameters(['includeRemoved' => '']));
        $routeMatch = new RouteMatch([]);
        $tableBuilder = $this->injectTableBuilder($serviceManager, TableEnum::LICENCE_VEHICLE_LIST_REMOVED);
        $tableBuilder->shouldReceive('getTotal')->andReturn(1);
        $expectedKey = sprintf('toggleRemovedVehiclesAction%s', ucfirst($type));
        $expectedTitle = sprintf('licence.vehicle.list.section.removed.action.hide-removed-vehicles.%s', $type);

        // Execute
        $result = $sut->indexAction($request, $routeMatch);

        // Assert
        $variables = (array) $result->getVariables();
        $this->assertArrayHasKey($expectedKey, $variables, sprintf('Expected result variables to have a key "%s"', $expectedKey));
        $this->assertEquals($expectedTitle, $variables[$expectedKey], sprintf('Expected result variable "%s" to have a value of "%s"', $expectedKey, $expectedTitle));
    }

    /**
     * Sets up default services.
     *
     * @return array
     */
    public function setUpDefaultServices()
    {
        return [
            TranslationHelperService::class => $this->setUpTranslator(),
            HandleQuery::class => $this->setUpQueryHandler(),
            Url::class => $this->setUpUrlHelper(),
            ResponseHelperService::class => $this->setUpResponseHelper(),
            TableFactory::class => (new TableFactoryMockBuilder())->build(),
            FormHelperService::class => $this->setUpFormHelper(),
        ];
    }

    /**
     * @param ServiceManager $serviceManager
     * @return ListVehicleController
     */
    protected function setUpSut(ServiceManager $serviceManager)
    {
        $translationService = $serviceManager->get(TranslationHelperService::class);
        $queryHandler = $serviceManager->get(HandleQuery::class);
        $urlHelper = $serviceManager->get(Url::class);
        $responseHelper = $serviceManager->get(ResponseHelperService::class);
        $tableFactory = $serviceManager->get(TableFactory::class);
        $formHelper = $serviceManager->get(FormHelperService::class);
        return new ListVehicleController($queryHandler, $translationService, $urlHelper, $responseHelper, $tableFactory, $formHelper);
    }

    /**
     * @return ServiceManager
     */
    protected function setUpServiceManager(): ServiceManager
    {
        return (new ServiceManagerBuilder($this))->build();
    }

    /**
     * @return HandleQuery
     */
    protected function setUpQueryHandler(): HandleQuery
    {
        $instance = m::mock(HandleQuery::class);
        $instance->shouldIgnoreMissing();
        $instance->shouldReceive('__invoke')->andReturn($this->setUpQueryResponse())->byDefault();
        return $instance;
    }

    /**
     * @param mixed $data
     * @return QueryResponse|MockInterface
     */
    protected function setUpQueryResponse($data = ['count' => 0, 'results' => []])
    {
        $response = m::mock(QueryResponse::class);
        $response->shouldIgnoreMissing();
        $response->shouldReceive('getResult')->andReturn($data)->byDefault();
        return $response;
    }

    /**
     * @return MockInterface
     */
    protected function setUpTranslator(): MockInterface
    {
        $instance = m::mock(TranslationHelperService::class);
        $instance->shouldIgnoreMissing('');
        return $instance;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return MockInterface
     */
    protected function resolveTranslator(ServiceLocatorInterface $serviceLocator): MockInterface
    {
        return $serviceLocator->get(TranslationHelperService::class);
    }

    /**
     * @return Url
     */
    protected function setUpUrlHelper(): Url
    {
        $instance = m::mock(Url::class);
        $instance->shouldIgnoreMissing('');
        return $instance;
    }

    /**
     * @return ResponseHelperService
     */
    protected function setUpResponseHelper(): ResponseHelperService
    {
        $instance = m::mock(ResponseHelperService::class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }

    /**
     * @return FormHelperService
     */
    protected function setUpFormHelper(): FormHelperService
    {
        $instance = m::mock(FormHelperService::class);
        $instance->shouldIgnoreMissing();
        return $instance;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $tableName
     * @param null $data
     * @param null $params
     * @return MockInterface
     */
    protected function injectTableBuilder(ServiceLocatorInterface $serviceLocator, string $tableName, $data = null, $params = null): MockInterface
    {
        $any = IsAnything::anything();
        $tableBuilder = (new TableBuilderMockBuilder())->build();
        $tableFactory = $serviceLocator->get(TableFactory::class);
        assert($tableFactory instanceof MockInterface, 'Expected instance of MockInterface');
        $tableFactory->byDefault()->shouldReceive('prepareTable')->with($tableName, $data ?? $any, $params ?? $any)->once()->andReturn($tableBuilder);
        return $tableBuilder;
    }
}
