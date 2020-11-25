<?php

namespace Olcs\Action\Licence\Vehicle;

use Common\Exception\BadRequestException;
use Common\Controller\Plugin\HandleCommand;
use Common\Exception\ResourceNotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Licence\TransferVehicles;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\Exception\Licence\LicenceVehicleLimitReachedException;
use Olcs\Exception\Licence\Vehicle\LicenceAlreadyAssignedVehicleException;
use Olcs\Form\Model\Form\Vehicle\Fieldset\YesNo;
use Olcs\Form\Model\Form\Vehicle\VehicleConfirmationForm;
use Olcs\Repository\Licence\LicenceRepository;
use Olcs\Repository\Licence\Vehicle\LicenceVehicleRepository;
use Olcs\Session\LicenceVehicleManagement;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Http\Response;
use Zend\Http\Request;
use Common\Exception\BailOutException;
use Zend\Mvc\Router\RouteMatch;

/**
 * @see TransferVehicleConfirmationStoreActionFactory
 */
class TransferVehicleConfirmationStoreAction extends TransferVehicleConfirmationAction
{
    /**
     * @var HandleCommand
     */
    protected $commandBus;

    /**
     * @param FlashMessengerHelperService $flashMessenger
     * @param TranslationHelperService $translationService
     * @param LicenceVehicleManagement $session
     * @param FormHelperService $formService
     * @param LicenceRepository $licenceRepository
     * @param LicenceVehicleRepository $licenceVehicleRepository
     * @param Url $urlPlugin
     * @param Redirect $redirectPlugin
     * @param HandleCommand $commandBus
     */
    public function __construct(
        FlashMessengerHelperService $flashMessenger,
        TranslationHelperService $translationService,
        LicenceVehicleManagement $session,
        FormHelperService $formService,
        LicenceRepository $licenceRepository,
        LicenceVehicleRepository $licenceVehicleRepository,
        Url $urlPlugin,
        Redirect $redirectPlugin,
        HandleCommand $commandBus
    )
    {
        $this->commandBus = $commandBus;
        parent::__construct($flashMessenger, $translationService, $session, $formService, $licenceRepository, $licenceVehicleRepository, $urlPlugin, $redirectPlugin);
    }

    /**
     * Handles a form submission from the confirmation page for transferring vehicles to a licence.
     *
     * @param RouteMatch $routeMatch
     * @param Request $request
     * @return Response
     * @throws BadRequestException
     * @throws BailOutException
     * @throws ResourceNotFoundException
     */
    public function __invoke(RouteMatch $routeMatch, Request $request)
    {
        $this->session->pullConfirmationFieldMessages();

        $currentLicenceId = (int) $routeMatch->getParam('licence');
        $currentLicence = $this->licenceRepository->findOneById($currentLicenceId);
        if (is_null($currentLicence)) {
            throw new ResourceNotFoundException();
        }

        $destinationLicenceId = $this->session->getDestinationLicenceId();
        if (is_null($destinationLicenceId)) {
            return $this->newRedirectToTransferIndexWithError(
                $currentLicenceId, 'licence.vehicle.transfer.confirm.error.no-destination-licence'
            );
        }

        $destinationLicence = $this->licenceRepository->findOneById($destinationLicenceId);
        if (is_null($destinationLicence)) {
            return $this->newRedirectToTransferIndexWithError(
                $currentLicenceId, 'licence.vehicle.transfer.confirm.error.invalid-destination'
            );
        }

        $vehicleIds = $this->session->getVrms();
        if (is_null($vehicleIds)) {
            return $this->newRedirectToTransferIndexWithError(
                $currentLicenceId, 'licence.vehicle.transfer.confirm.error.no-vehicles'
            );
        }

        $input = (array) $request->getPost();
        $requestedAction = $input[VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME][VehicleConfirmationForm::FIELD_OPTIONS_NAME] ?? null;
        if (empty($requestedAction)) {
            $this->session->setConfirmationFieldMessages(['licence.vehicle.transfer.confirm.validation.select-an-option']);
            return $this->newRedirectToTransferIndex($currentLicenceId);
        }
        if ($requestedAction !== YesNo::OPTION_YES) {
            return $this->newRedirectToTransferIndex($currentLicenceId);
        }

        try {
            $this->transferVehicles($currentLicenceId, $vehicleIds, $destinationLicence);
        } catch(LicenceVehicleLimitReachedException $exception) {
            $message = $this->translator->translateReplace(
                'licence.vehicles_transfer.form.message_exceed',
                [$exception->getLicenceNumber()]
            );
            return $this->newRedirectToTransferIndexWithError($currentLicenceId, $message);
        } catch(LicenceAlreadyAssignedVehicleException $exception) {
            $vehicleVrms = $exception->getVehicleVrms();
            if (count($vehicleVrms) === 1) {
                $message = 'licence.vehicles_transfer.form.message_already_on_licence_singular';
                $data = [array_values($vehicleVrms)[0], $exception->getLicenceNumber()];
            } else {
                $message = 'licence.vehicles_transfer.form.message_already_on_licence';
                $data = [implode(', ', $vehicleVrms), $exception->getLicenceNumber()];
            }
            return $this->newRedirectToTransferIndexWithError(
                $currentLicenceId, $this->translator->translateReplace($message, $data)
            );
        }

        $this->flashTransferOfVehiclesCompleted($destinationLicence, $vehicleIds);
        $this->flashIfLicenceHasNoVehicles($currentLicence);
        return $this->newRedirectToSwitchboard($currentLicenceId);
    }

    /**
     * Flashes a message to the user when a licence with a given id has no vehicles.
     *
     * @param LicenceDTO $licence
     */
    protected function flashIfLicenceHasNoVehicles(LicenceDTO $licence)
    {
        $activeVehicleCount = $licence->getActiveVehicleCount();
        if (null !== $activeVehicleCount && $activeVehicleCount < 1) {
            $message = $this->translator->translate('licence.vehicle.transfer.confirm.success.last-vehicle-transferred');
            $this->flashMessenger->addSuccessMessage($message);
        }
    }

    /**
     * Flashes a success message to signal that vehicles with the given ids have been transferred to a destination
     * licence.
     *
     * @param LicenceDTO $destinationLicence
     * @param array $vehicleIds
     */
    protected function flashTransferOfVehiclesCompleted(LicenceDTO $destinationLicence, array $vehicleIds)
    {
        if (count($vehicleIds) === 1) {
            $message = $this->translator->translateReplace(
                'licence.vehicle.transfer.confirm.success.singular',
                [$destinationLicence->getLicenceNumber()]
            );
        } else {
            $message = $this->translator->translateReplace(
                'licence.vehicle.transfer.confirm.success.plural',
                [count($vehicleIds), $destinationLicence->getLicenceNumber()]
            );
        }
        $this->flashMessenger->addSuccessMessage($message);
    }

    /**
     * Transfers one or more vehicles to a destination licence.
     *
     * @param int $currentLicenceId
     * @param array $vehicleIds
     * @param LicenceDTO $destinationLicence
     * @throws BadRequestException
     * @throws LicenceAlreadyAssignedVehicleException
     * @throws LicenceVehicleLimitReachedException
     * @throws BailOutException
     */
    protected function transferVehicles(int $currentLicenceId, array $vehicleIds, LicenceDTO $destinationLicence)
    {
        // @todo relocate this logic to a command handler class

        $response = $this->commandBus->__invoke(TransferVehicles::create([
            'id' => $currentLicenceId,
            'target' => $destinationLicence->getId(),
            'licenceVehicles' => $vehicleIds,
        ]));
        $errors = $response->getResult()['messages'] ?? null;
        $errors = is_array($errors) ? $errors : [];
        if ($response->isClientError() && count($errors) > 0) {
            if (isset($errors['LIC_TRAN_1'])) {
                throw new LicenceVehicleLimitReachedException(
                    $destinationLicence->getId(),
                    $destinationLicence->getLicenceNumber()
                );
            }
            if (isset($errors['LIC_TRAN_2']) || isset($errors['LIC_TRAN_3'])) {
                $invalidVehiclesJson = isset($errors['LIC_TRAN_2']) ? $errors['LIC_TRAN_2'] : $errors['LIC_TRAN_3'];
                $invalidVehicleVrms = json_decode($invalidVehiclesJson, true);
                throw new LicenceAlreadyAssignedVehicleException(
                    $destinationLicence->getId(),
                    $destinationLicence->getLicenceNumber(),
                    $invalidVehicleVrms
                );
            }
            throw new BadRequestException('Unexpected error when executing a command');
        }
        if (! $response->isOk()) {
            throw new BadRequestException('Unexpected response status received when executing a command');
        }
    }
}
