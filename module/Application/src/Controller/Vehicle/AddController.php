<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Application\Controller\Vehicle\Factory\AddControllerFactory;
use Dvsa\Olcs\Application\Session\Vehicles;
use Dvsa\Olcs\Transfer\Command\Application\CreateGoodsVehicle;
use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle;
use Laminas\Http\Request;
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
    /**
     * @var HandleCommand
     */
    private $commandHandler;

    /**
     * @var FormHelperService
     */
    private $formHelper;

    /**
     * @var HandleQuery
     */
    private $queryHandler;
    /**
     * @var Vehicles
     */
    private $session;

    /**
     * @var Url
     */
    private $urlHelper;
    /**
     * @var Redirect
     */
    private $redirectHelper;

    /**
     * AddController constructor.
     * @param HandleCommand $commandHandler
     * @param FormHelperService $formHelper
     * @param HandleQuery $queryHandler
     * @param Redirect $redirectHelper
     * @param Vehicles $session
     * @param Url $urlHelper
     */
    public function __construct(
        HandleCommand $commandHandler,
        FormHelperService $formHelper,
        HandleQuery $queryHandler,
        Redirect $redirectHelper,
        Vehicles $session,
        Url $urlHelper
    )
    {
        $this->commandHandler = $commandHandler;
        $this->formHelper = $formHelper;
        $this->queryHandler = $queryHandler;
        $this->redirectHelper = $redirectHelper;
        $this->session = $session;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @return ViewModel
     */
    public function indexAction(Request $request): ViewModel
    {
        $view = new ViewModel();
        $view->setTemplate('pages/vehicle/add');

        $form = $this->formHelper->createForm(AddVehicleSearch::class);
        if ($request->isPost()){
            $form->setData($request->getPost()->toArray());
            $form->isValid();
        }
        $form->prepare();

        $variables =  [
        'title' => 'Add a vehicle',
        'searchForm' => $form,
        'backLink' => $this->urlHelper->fromRoute('lva-application/vehicles/ocrs/GET', [], [], true)
    ];

        $view->setVariables($variables);

        return $view;
    }

    /**
     * @param Request $request
     */
    public function searchAction(Request $request)
    {
        $formData = $request->getPost()->toArray();

        $searchForm = $this->formHelper->createForm(AddVehicleSearch::class);
        $searchForm->setData($formData);

        if (!$searchForm->isValid()) {
            return $this->indexAction($request);
        }

        $vehicleData = $this->getVehicleData($formData['vehicle-search']['search-value']);
        $this->session->setVehicleData($vehicleData);

        $confirmationForm = $this->formHelper->createForm(ConfirmVehicle::class);
        $confirmationForm->setAttribute('action', $this->urlHelper->fromRoute('lva-application/vehicles/add/confirmation', [], [], true));

        $confirmationForm->prepare();
        $searchForm->prepare();

        $view = new ViewModel();
        $view->setTemplate('pages/vehicle/add');

        $variables =  [
            'title' => 'Add a vehicle search results',
            'searchForm' => $searchForm,
            'confirmationForm' => $confirmationForm,
            'vehicleData' => $vehicleData,
            'backLink' => $this->urlHelper->fromRoute('lva-application/vehicles/ocrs/GET', [], [], true)
        ];

        $view->setVariables($variables);

        return $view;


    }

    public function confirmationAction(Request $request, RouteMatch $routeMatch)
    {
        if (!$this->session->hasVehicleData()) {
            return $this->redirectHelper->toRoute('lva-application/vehicles/add/GET', [], [], true);
        }

        $vehicleData = $this->session->getVehicleData();

        $dtoData = [
            'id' => $routeMatch->getParam('application'),
            'vrm' => $vehicleData["registrationNumber"],
            'platedWeight' => $vehicleData["revenueWeight"],
        ];

        $command = CreateGoodsVehicle::create($dtoData);

        $response = $this->commandHandler->__invoke($command);

        if ($response->isOk()) {
            echo "vehicle added";
            var_dump($response->getResult());
            $this->session->destroy();
            die();
        } else {
            var_dump($response->getStatusCode(), $response->getResult());
            die();
        }
    }

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
}
