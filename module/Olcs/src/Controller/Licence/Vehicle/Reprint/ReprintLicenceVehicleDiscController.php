<?php

namespace Olcs\Controller\Licence\Vehicle\Reprint;

use Olcs\Controller\Licence\Vehicle\AbstractVehicleController;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\Vehicles as VehiclesForm;
use Common\Form\Form;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use Laminas\Http\Response;

/**
 * @see ReprintVehicleLicenceControllerFactory
 */
class ReprintLicenceVehicleDiscController extends AbstractVehicleController
{
    protected const MAX_ACTION_BATCH_SIZE = 20;

    /**
     * @inheritDoc
     */
    protected $formConfig = [
        'default' => [
            'searchForm' => [
                'formClass' => ListVehicleSearch::class
            ],
            'goodsVehicleForm' => [
                'formClass' => VehiclesForm::class,
            ]
        ]
    ];

    /**
     * Handles a request from a user to show the form to reprint one or more of the licences that they have access to.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        $form = $this->form;
        $this->configureFormActionsForIndex($form);

        $vehicleTable = $this->createVehicleTable();
        $tableFieldset = $form->get('table');
        $tableFieldset->get('table')->setTable($vehicleTable);
        $tableFieldset->get('rows')->setValue(count($vehicleTable->getRows()));

        $view = $this->genericView();
        $view->setVariables([
            'title' => $this->isSearchResultsPage() ? 'licence.vehicle.reprint.search.header' : 'licence.vehicle.reprint.header',
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'clearUrl' => $this->getLink('licence/vehicle/reprint/GET'),
            'form' => $form,
            'backLink' => $this->getLink('licence/vehicle/GET'),
            'bottomContent' => $this->getChooseDifferentActionMarkup()
        ]);

        if ($vehicleTable->getTotal() > static::DEFAULT_TABLE_ROW_LIMIT) {
            $view->setVariable('note', $this->translator->translateReplace('licence.vehicle.reprint.note', [static::MAX_ACTION_BATCH_SIZE]));
        }

        if ($vehicleTable->getTotal() > static::DEFAULT_TABLE_ROW_LIMIT || $this->isSearchResultsPage()) {
            $searchForm = $this->forms['searchForm'];
            $this->configureSearchFormForIndex($searchForm, $request);
            $view->setVariable('searchForm', $searchForm);
        }

        return $view;
    }

    /**
     * Handles a request from a user to reprint one or more of the licences that they can access.
     *
     * @return Response|ViewModel
     */
    public function postAction()
    {
        $action = array_keys($this->getRequest()->getPost('formActions'))[0];

        if ($action !== 'action') {
            return $this->nextStep('licence/vehicle/GET');
        }

        $selectedVehicles = $this->getRequest()->getPost('table')['id'] ?? null;

        if (empty($selectedVehicles)) {
            $this->form->get('formActions')->setMessages(['licence.vehicle.reprint.error.none-selected']);
            return $this->indexAction();
        }

        if (count($selectedVehicles) > static::MAX_ACTION_BATCH_SIZE) {
            $message = $this->translator->translateReplace('licence.vehicle.reprint.error.too-many-selected', [static::MAX_ACTION_BATCH_SIZE]);
            $this->form->get('formActions')->get('action')->setMessages([$message]);
            return $this->indexAction();
        }

        $this->session->setVrms($selectedVehicles);
        return $this->nextStep('licence/vehicle/reprint/confirm/GET');
    }

    /**
     * @param Form $form
     */
    protected function configureFormActionsForIndex(Form $form)
    {
        $form->get('formActions')
            ->get('action')
            ->setLabel('licence.vehicle.reprint.button.action.label')
            ->setAttribute('title', 'licence.vehicle.reprint.button.action.title');
        $form->get('formActions')
            ->get('cancel')
            ->setAttribute('title', 'licence.vehicle.reprint.button.cancel.title');
    }

    /**
     * @param Form $form
     * @param Request $request
     */
    protected function configureSearchFormForIndex(Form $form, Request $request)
    {
        $form->get('vehicleSearch')
            ->setOption('legend', 'licence.vehicle.table.search.reprint.legend');

        $formData = $request->getQuery();
        $form->setData($formData);

        if (array_key_exists('vehicleSearch', $formData)) {
            $form->isValid();
        }

        $form->remove('security');
    }
}
