<?php

namespace Olcs\Action\Licence\Vehicle;

use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Form\Model\Form\Vehicle\VehicleConfirmationForm;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
use Olcs\Exception\Licence\Vehicle\VehiclesNotFoundWithIdsException;
use Zend\Http\Request;
use Zend\Mvc\Router\RouteMatch;

/**
 * @see TransferVehicleConfirmationIndexActionFactory
 * @see TransferVehicleConfirmationIndexActionTest
 */
class TransferVehicleConfirmationIndexAction extends TransferVehicleConfirmationAction
{
    /**
     * Handles a request from a user to view the confirmation page for transferring one or more vehicles to a license.
     *
     * @param RouteMatch $routeMatch
     * @param Request $request
     * @return ViewModel|Response
     */
    public function __invoke(RouteMatch $routeMatch, Request $request)
    {
        $currentLicenceId = (int) $routeMatch->getParam('licence');
        $currentLicence = $this->licenceRepository->findOneById($currentLicenceId);
        if (is_null($currentLicence)) {
            return $this->newRedirectToTransferIndexWithError(
                $currentLicenceId, 'licence.vehicle.transfer.confirm.error.invalid-licence'
            );
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

        try {
            $licenceVehicles = $this->licenceVehicleRepository->findByVehicleId($vehicleIds);
        } catch (VehiclesNotFoundWithIdsException $exception) {
            return $this->newRedirectToTransferIndexWithError(
                $currentLicenceId, 'licence.vehicle.transfer.confirm.error.invalid-vehicles'
            );
        }

        $viewData = [
            'licNo' => $currentLicence->getLicenceNumber(),
            'form' => $this->createForm(VehicleConfirmationForm::class, $request),
            'backLink' => $this->urlPlugin->fromRoute(static::ROUTE_TRANSFER_INDEX, [], [], true),
            'bottomContent' => $this->translator->translateReplace('licence.vehicle.generic.choose-different-action', [
                $this->urlPlugin->fromRoute('licence/vehicle/GET', [], [], true),
            ]),
            'destinationLicenceId' => $destinationLicence->getId(),
            'vrmList' => array_map(function (LicenceVehicleDTO  $licenceVehicle) {
                return $licenceVehicle->getVehicle()->getVrm();
            }, $licenceVehicles),
        ];

        if (count($licenceVehicles) !== 1) {
            $confirmHeaderKey = 'licence.vehicle.transfer.confirm.header.plural';
            $viewData['vrmListInfoText'] = 'licence.vehicle.transfer.confirm.list.hint.plural';
        } else {
            $confirmHeaderKey = 'licence.vehicle.transfer.confirm.header.singular';
            $viewData['vrmListInfoText'] = 'licence.vehicle.transfer.confirm.list.hint.singular';
        }
        $destinationLicenceNumber = $destinationLicence->getLicenceNumber();
        $viewData['title'] = $this->translator->translateReplace($confirmHeaderKey, [$destinationLicenceNumber]);

        return $this->newPageView('pages/licence-vehicle', $viewData);
    }
}
