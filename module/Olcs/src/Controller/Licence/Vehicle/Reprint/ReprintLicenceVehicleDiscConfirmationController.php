<?php

namespace Olcs\Controller\Licence\Vehicle\Reprint;

use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\Vehicle\ReprintDisc;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Olcs\Controller\Licence\Vehicle\AbstractVehicleController;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Exception\Licence\Vehicle\VehiclesNotFoundWithIdsException;
use Olcs\Form\Model\Form\Vehicle\VehicleConfirmationForm;
use Exception;
use Laminas\Http\Response;

class ReprintLicenceVehicleDiscConfirmationController extends AbstractVehicleController
{
    /**
     * @inheritDoc
     */
    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => VehicleConfirmationForm::class,
            ]
        ]
    ];

    /**
     * Handles a request from a user to view a page from which they can confirm the reprint of one or more licences
     * for vehicles that they have access to.
     *
     * @return mixed
     * @throws Exception
     */
    public function indexAction()
    {
        if (!$this->session->hasVrms()) {
            // Redirect to add action if VRMs are not in session.
            $this->flashMessenger->addErrorMessage('licence.vehicle.reprint.confirm.error.no-vehicles');
            return $this->nextStep('licence/vehicle/reprint/GET');
        }
        $licenceVehicles = $this->getLicenceVehiclesByVehicleId($this->session->getVrms());
        $viewParams = [
            'title' => 'licence.vehicle.reprint.confirm.header.singular',
            'licNo' => $this->data['licence']['licNo'],
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/reprint/GET'),
            'bottomContent' => $this->getChooseDifferentActionMarkup(),
            'vrmList' => array_map(function (LicenceVehicleDTO $licenceVehicle) {
                return $licenceVehicle->getVehicle()->getVrm();
            }, $licenceVehicles),
            'vrmListInfoText' => 'licence.vehicle.reprint.confirm.list.hint.singular'
        ];

        if (count($licenceVehicles) > 1) {
            $viewParams['title'] = 'licence.vehicle.reprint.confirm.header.plural';
            $viewParams['vrmListInfoText'] = 'licence.vehicle.reprint.confirm.list.hint.plural';
        }

        return $this->renderView($viewParams);
    }

    /**
     * Handles a request from a user to reprint one or more licences for vehicles that they have access to.
     *
     * @return Response
     * @throws Exception
     */
    public function postAction()
    {
        if (!$this->session->hasVrms()) {
            // Redirect to reprint action if VRMs are not in session.
            $this->flashMessenger->addErrorMessage('licence.vehicle.reprint.confirm.error.no-vehicles');
            return $this->nextStep('licence/vehicle/reprint/GET');
        }

        $input = (array) $this->getRequest()->getPost();
        $this->form->setData($input);
        if (! $this->form->isValid()) {
            return $this->indexAction();
        }

        $selectedOption = $input[VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME][VehicleConfirmationForm::FIELD_OPTIONS_NAME] ?? '';
        if (empty($selectedOption)) {
            $this->form
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_NAME)
                ->setMessages([
                    'licence.vehicle.reprint.confirm.validation.select-an-option'
                ]);
            return $this->indexAction();
        }

        // Has the user selected no?
        if ($selectedOption != 'yes') {
            return $this->nextStep('licence/vehicle/reprint/GET');
        }

        $vehicleIds = $this->session->getVrms();
        $this->handleCommand(ReprintDisc::create(['ids' => $vehicleIds]));

        $successMessageKey = 'licence.vehicle.reprint.confirm.success.singular';
        if (count($vehicleIds) > 1) {
            $successMessageKey = 'licence.vehicle.reprint.confirm.success.plural';
        }

        $panelMessage = $this->translator->translateReplace($successMessageKey, [count($vehicleIds)]);
        $this->flashMessenger->addMessage($panelMessage, SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE);

        return $this->nextStep('licence/vehicle/GET');
    }

    /**
     * @param array<int> $vehicleIds
     * @return array<LicenceVehicleDTO>
     * @throws VehiclesNotFoundWithIdsException
     */
    protected function getLicenceVehiclesByVehicleId(array $vehicleIds): array
    {
        if (empty($vehicleIds)) {
            return [];
        }
        $query = LicenceVehiclesById::create(['ids' => $vehicleIds]);
        try {
            $queryResult = $this->handleQuery($query);
        } catch (NotFoundException|AccessDeniedException $exception) {
            throw new VehiclesNotFoundWithIdsException($vehicleIds);
        }
        $licenceVehicles = $queryResult->getResult()['results'] ?? [];
        return array_map(function ($licenceVehicle) {
            return new LicenceVehicleDTO($licenceVehicle);
        }, $licenceVehicles);
    }
}
