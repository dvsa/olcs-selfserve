<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicles;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Application\Controller\Vehicles\Factory\AddControllerFactory;
use Dvsa\Olcs\Application\Session\Vehicles;
use Dvsa\Olcs\Transfer\Command\Application\CreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle;
use Exception;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use Olcs\Form\Model\Form\Vehicle\AddVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\ConfirmVehicle;

/**
 * @See AddControllerFactory
 */
class AddController
{
    const ROUTE_APPLICATION_VEHICLES_ADD = 'lva-application/vehicles/add/GET';
    const ROUTE_APPLICATION_VEHICLES_ADD_CONFIRMATION = 'lva-application/vehicles/add/confirmation';
    const ROUTE_APPLICATION_VEHICLES_OCRS = 'lva-application/vehicles/ocrs/GET';
    const ROUTE_APPLICATION_VEHICLES = 'lva-application/vehicles';

    /**
     * @var HandleCommand
     */
    private $commandHandler;

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
     * @var Vehicles
     */
    private $session;

    /**
     * @var TranslationHelperService
     */
    private $translator;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * AddController constructor.
     * @param HandleCommand $commandHandler
     * @param FlashMessenger $flashMessenger
     * @param FormHelperService $formHelper
     * @param HandleQuery $queryHandler
     * @param Redirect $redirectHelper
     * @param Vehicles $session
     * @param TranslationHelperService $translator
     * @param Url $urlHelper
     */
    public function __construct(
        HandleCommand $commandHandler,
        FlashMessenger $flashMessenger,
        FormHelperService $formHelper,
        HandleQuery $queryHandler,
        Redirect $redirectHelper,
        Vehicles $session,
        TranslationHelperService $translator,
        Url $urlHelper
    )
    {
        $this->commandHandler = $commandHandler;
        $this->flashMessenger = $flashMessenger;
        $this->formHelper = $formHelper;
        $this->queryHandler = $queryHandler;
        $this->redirectHelper = $redirectHelper;
        $this->session = $session;
        $this->translator = $translator;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @return ViewModel
     */
    public function indexAction(Request $request): ViewModel
    {
        $view = new ViewModel();
        $view->setTemplate('pages/vehicle/add');

        $searchForm = $this->createSearchForm($request->getPost()->toArray());

        if ($request->isPost() && $searchForm->isValid() && $this->session->hasVehicleData()) {
            $confirmationForm = $this->formHelper->createForm(ConfirmVehicle::class);
            $confirmationForm->setAttribute(
                'action',
                $this->urlHelper->fromRoute(static::ROUTE_APPLICATION_VEHICLES_ADD_CONFIRMATION, [], [], true)
            );
            $confirmationForm->prepare();
        }

        $view->setVariables([
            'title' => 'Add a vehicle',
            'searchForm' => $searchForm,
            'confirmationForm' => $confirmationForm ?? null,
            'vehicleData' => $this->session->getVehicleData(),
            'backLink' => $this->urlHelper->fromRoute(static::ROUTE_APPLICATION_VEHICLES_OCRS, [], [], true)
        ]);

        return $view;
    }

    /**
     * @param Request $request
     * @return ViewModel
     */
    public function searchAction(Request $request): ViewModel
    {
        $formData = $request->getPost()->toArray();

        $searchForm = $this->createSearchForm($formData);

        if (!$searchForm->isValid()) {
            return $this->indexAction($request);
        }

        try {
            $vehicleData = $this->getVehicleData($formData['vehicle-search']['search-value']);
            $this->session->setVehicleData($vehicleData);
        } catch (NotFoundException $exception) {
            $this->session->markVehicleNotFound();
        } catch (Exception $exception) {
            $this->flashMessenger->addErrorMessage(
                $this->translator->translate('licence.vehicle.add.search.query-error')
            );
        } finally {
            return $this->indexAction($request);
        }
    }

    /**
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response
     * @throws \Common\Exception\BailOutException
     */
    public function confirmationAction(Request $request, RouteMatch $routeMatch): Response
    {
        if (!$this->session->hasVehicleData()) {
            return $this->redirectHelper->toRoute(static::ROUTE_APPLICATION_VEHICLES_ADD, [], [], true);
        }

        $vehicleData = $this->session->getVehicleData();
        $this->session->destroy();

        // TODO: Pull into method
        $dtoData = [
            'id' => $routeMatch->getParam('application'),
            'vrm' => $vehicleData["registrationNumber"],
            'platedWeight' => $vehicleData["revenueWeight"],
        ];

        $command = CreateGoodsVehicle::create($dtoData);

        $response = $this->commandHandler->__invoke($command);

        if ($response->isOk()) {
            $this->flashMessenger->addSuccessMessage($this->translator->translateReplace('licence.vehicle.add.success', [$dtoData['vrm']]));
            return $this->redirectHelper->toRoute(static::ROUTE_APPLICATION_VEHICLES, [], [], true);
        }

        if (isset($response->getResult()['messages']['VE-VRM-2'])) {
            die("Duplicate vehicle functionality not yet implemented");
        }

        $message = array_values($response->getResult()['messages']['vrm'])[0];
        $this->flashMessenger->addErrorMessage($message);

        return $this->redirectHelper->toRoute(static::ROUTE_APPLICATION_VEHICLES_ADD, [], [], true);

    }

    /**
     * @param string $vrm
     * @return array
     * @throws NotFoundException|Exception
     */
    protected function getVehicleData(string $vrm): array
    {
        $response = $this->queryHandler->__invoke(Vehicle::create(['vrm' => $vrm]));

        if (!$response->isOk()) {
            throw new Exception("Bad response: " . $response->getStatusCode());
        }

        if ($response->getResult()['count'] === 0) {
            throw new NotFoundException("Vehicle not found with vrm: $vrm");
        }

        return $response->getResult()['results'][0];
    }

    /**
     * @param array|null $data
     * @return \Common\Form\Form
     */
    protected function createSearchForm(?array $data): \Common\Form\Form
    {
        $searchForm = $this->formHelper->createForm(AddVehicleSearch::class);
        $searchForm->setData($data);

        if ($this->session->wasVehicleNotFound()) {
            $searchForm->get('vehicle-search')->setMessages([
                AbstractInputSearch::ELEMENT_INPUT_NAME => [
                    'vrm_not_found' => $this->translator->translate('licence.vehicle.add.search.vrm-not-found')
                ]
            ]);
        }

        $searchForm->prepare();

        return $searchForm;
    }
}
