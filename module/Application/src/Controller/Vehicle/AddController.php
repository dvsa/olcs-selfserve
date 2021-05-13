<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Vehicle;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
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
     * AddController constructor.
     * @param HandleCommand $commandHandler
     * @param FormHelperService $formHelper
     * @param HandleQuery $queryHandler
     * @param Vehicles $session
     * @param Url $urlHelper
     */
    public function __construct(
        HandleCommand $commandHandler,
        FormHelperService $formHelper,
        HandleQuery $queryHandler,
        Vehicles $session,
        Url $urlHelper
    )
    {
        $this->commandHandler = $commandHandler;
        $this->formHelper = $formHelper;
        $this->queryHandler = $queryHandler;
        $this->session = $session;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @return ViewModel
     */
    public function indexAction(): ViewModel
    {
        $view = new ViewModel();
        $view->setTemplate('pages/vehicle/add');

        $form = $this->formHelper->createForm(AddVehicleSearch::class);
        $form->prepare();

        $variables =  [
        'title' => 'Add a vehicle',
        'searchForm' => $form,
        'backLink' => ''//TODO: Add link to OCRS once it's done
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
        $vehicleData = $this->getVehicleData($formData['vehicle-search']['search-value']);

        $this->session->setVehicleData($vehicleData);

        $view = new ViewModel();
        $view->setTemplate('pages/vehicle/add');

        $searchForm = $this->formHelper->createForm(AddVehicleSearch::class);
        $searchForm->setData($formData);
        $searchForm->prepare();

        $confirmationForm = $this->formHelper->createForm(ConfirmVehicle::class);
        $confirmationForm->setAttribute('action', $this->urlHelper->fromRoute('lva-application/vehicles/add/confirmation', [], [], true));


        $variables =  [
            'title' => 'Add a vehicle search results',
            'searchForm' => $searchForm,
            'confirmationForm' => $confirmationForm,
            'vehicleData' => $vehicleData,
            'backLink' => ''//TODO: Add link to OCRS once it's done
        ];

        $view->setVariables($variables);

        return $view;


    }

    public function confirmationAction(Request $request, RouteMatch $routeMatch)
    {
        if (!$this->session->hasVehicleData()) {
            // Redirect to add page
            echo "MISSING SESSION";
            die();
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
