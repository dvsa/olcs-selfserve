<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Exception\BadRequestException;
use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Licence\TransferVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Olcs\Controller\Controller;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Exception\Licence\LicenceNotFoundWithIdException;
use Olcs\Exception\Licence\LicenceVehicleLimitReachedException;
use Olcs\Exception\Licence\Vehicle\LicenceAlreadyAssignedVehicleException;
use Olcs\Form\Model\Form\Vehicle\Fieldset\YesNo;
use Olcs\Form\Model\Form\Vehicle\VehicleConfirmationForm;
use Olcs\Repository\Licence\LicenceRepository;
use Olcs\Repository\Licence\Vehicle\LicenceVehicleRepository;
use Olcs\Session\LicenceVehicleManagement;
use Zend\Mvc\MvcEvent;
use Olcs\Exception\Licence\Vehicle\VehicleSelectionEmptyException;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
use Exception;
use Olcs\Exception\Licence\Vehicle\DestinationLicenceNotSetException;
use Olcs\Exception\Licence\Vehicle\DestinationLicenceNotFoundWithIdException;
use Olcs\Exception\Licence\Vehicle\VehiclesNotFoundWithIdsException;
use Zend\Http\Request;

/**
 * @see TransferVehicleConfirmationControllerFactory
 */
class TransferVehicleConfirmationController extends Controller
{
    protected const ROUTE_TRANSFER_INDEX = 'licence/vehicle/transfer/GET';

    /**
     * @var FlashMessengerHelperService
     */
    protected $flashMessenger;

    /**
     * @var TranslationHelperService
     */
    protected $translator;

    /**
     * @var LicenceVehicleManagement
     */
    protected $session;

    /**
     * @var HandleCommand
     */
    protected $commandBus;

    /**
     * @var HandleQuery
     */
    protected $queryBus;

    /**
     * @var FormHelperService
     */
    protected $formService;

    /**
     * @var LicenceRepository
     */
    protected $licenceRepository;

    /**
     * @var LicenceVehicleRepository
     */
    protected $licenceVehicleRepository;

    /**
     * @param FlashMessengerHelperService $flashMessenger
     * @param TranslationHelperService $translationService
     * @param LicenceVehicleManagement $session
     * @param HandleCommand $commandBus
     * @param FormHelperService $formService
     * @param LicenceRepository $licenceRepository
     * @param LicenceVehicleRepository $licenceVehicleRepository
     */
    public function __construct(
        FlashMessengerHelperService $flashMessenger,
        TranslationHelperService $translationService,
        LicenceVehicleManagement $session,
        HandleCommand $commandBus,
        FormHelperService $formService,
        LicenceRepository $licenceRepository,
        LicenceVehicleRepository $licenceVehicleRepository
    )
    {
        $this->flashMessenger = $flashMessenger;
        $this->translator = $translationService;
        $this->session = $session;
        $this->commandBus = $commandBus;
        $this->formService = $formService;
        $this->licenceRepository = $licenceRepository;
        $this->licenceVehicleRepository = $licenceVehicleRepository;

        // @todo persist form messages in session so that redirects can be used

        // @todo extract controller logic to session handler

        // @todo implement query handler support

        // @todo extract controller logic to query handlers

        // @todo implement command handler support

        // @todo extract controller logic to command handlers
    }

    /**
     * @inheritDoc
     */
    public function onDispatch(MvcEvent $e)
    {
        try {
            return parent::onDispatch($e);
        } catch (VehicleSelectionEmptyException $exception) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.no-vehicles');
        } catch (VehiclesNotFoundWithIdsException $exception) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-vehicles');
        } catch (DestinationLicenceNotSetException $exception) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.no-destination-licence');
        } catch (DestinationLicenceNotFoundWithIdException $exception) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-destination');
        } catch (LicenceNotFoundWithIdException $exception) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-licence');
        } catch (LicenceVehicleLimitReachedException $exception) {
            $this->flashMessenger->addErrorMessage($this->translator->translateReplace(
                'licence.vehicles_transfer.form.message_exceed',
                [$exception->getLicenceNumber()]
            ));
        } catch (LicenceAlreadyAssignedVehicleException $exception) {
            $vehicleVrms = $exception->getVehicleVrms();
            if (count($vehicleVrms) === 1) {
                $message = 'licence.vehicles_transfer.form.message_already_on_licence_singular';
                $data = [array_values($vehicleVrms)[0], $exception->getLicenceNumber()];
            } else {
                $message = 'licence.vehicles_transfer.form.message_already_on_licence';
                $data = [implode(', ', $vehicleVrms), $exception->getLicenceNumber()];
            }
            $this->flashMessenger->addErrorMessage($this->translator->translateReplace($message, $data));
        }
        return $this->redirectToLicenceTransferIndex();
    }

    /**
     * Creates a response to redirect to the transfer vehicles index page.
     *
     * @return Response
     */
    protected function redirectToLicenceTransferIndex()
    {
        $licenceId = (int) $this->params('licence');
        return $this->redirect()->toUrl(sprintf('/licence/%s/vehicle/transfer', $licenceId));
    }

    /**
     * Handles a request from a user to view the confirmation page for transferring one or more vehicles to a license.
     *
     * @param RouteMatch $routeMatch
     * @param Request $request
     * @return ViewModel
     * @throws DestinationLicenceNotFoundWithIdException
     * @throws DestinationLicenceNotSetException
     * @throws LicenceNotFoundWithIdException
     * @throws VehiclesNotFoundWithIdsException
     * @throws LicenceNotFoundWithIdException
     * @throws VehicleSelectionEmptyException
     * @throws VehiclesNotFoundWithIdsException
     */
    public function indexAction(RouteMatch $routeMatch, Request $request)
    {
        $currentLicence = $this->licenceRepository->findOneById((int) $routeMatch->getParam('licence'));
        $destinationLicence = $this->resolveDestinationLicence();
        $destinationLicenceNumber = $destinationLicence->getLicenceNumber();
        $licenceVehicles = $this->licenceVehicleRepository->findByVehicleId($this->resolveVehicleIdsFromSession());
        $viewData = [
            'licNo' => $currentLicence->getLicenceNumber(),
            'form' => $this->createForm(VehicleConfirmationForm::class, $this->getRequest()),

            // @todo inject url builder as dependency
//            'backLink' => $this->url()->fromRoute(static::ROUTE_TRANSFER_INDEX, [], [], true),
            'bottomContent' => $this->translator->translateReplace('licence.vehicle.generic.choose-different-action', [

                // @todo inject url builder as dependency
//                $this->url()->fromRoute('licence/vehicle/GET', [], [], true),
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
        $viewData['title'] = $this->translator->translateReplace($confirmHeaderKey, [$destinationLicenceNumber]);
        return $this->renderView('pages/licence-vehicle', $viewData);
    }

    /**
     * Handles a form submission from the confirmation page for transferring vehicles to a licence.
     *
     * @return Response|ViewModel
     * @throws Exception
     */
    public function postAction()
    {
        $licenceId = (int) $this->params('licence');
        $vehicleIds = $this->resolveVehicleIdsFromSession();
        $destinationLicence = $this->resolveDestinationLicence();
        $input = (array) $this->getRequest()->getPost();
        $form = $this->createForm(VehicleConfirmationForm::class, $this->getRequest());
        if (! $form->isValid()) {

            // @todo this will require redirects to work with flash messages!
            return $this->redirectToLicenceTransferIndex();
        }
        $requestedAction = $input[VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME][VehicleConfirmationForm::FIELD_OPTIONS_NAME] ?? null;
        if (empty($requestedAction)) {
            $confirmationField = $form
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_NAME);
            $confirmationField->setMessages(['licence.vehicle.transfer.confirm.validation.select-an-option']);
            return $this->redirectToLicenceTransferIndex();
        }

        if ($requestedAction !== YesNo::OPTION_YES) {
            return $this->redirectToLicenceTransferIndex();
        }

        $this->transferVehicles($licenceId, $vehicleIds, $destinationLicence);
        $this->flashTransferOfVehiclesCompleted($destinationLicence, $vehicleIds);
        $this->flashIfLicenceHasNoVehicles($licenceId);

        return $this->redirect()->toUrl(sprintf('/licence/%s/vehicle', $licenceId));
    }

    // @todo this should be moved out to a trait?
    protected function createForm(string $className, \Zend\Http\Request $request)
    {
        $form = $this->formService->createForm($className, true, false);
        $this->formService->setFormActionFromRequest($form, $request);
        if ($request->isPost()) {
            $form->setData((array) $this->getRequest()->getPost());
        }
        return $form;
    }

    // @todo this should be moved to a trait InteractsWithViewsTrait
    protected function renderView(string $templateName, array $params): ViewModel
    {
        $content = new ViewModel($params);
        $content->setTemplate($templateName);
        $view = new ViewModel();
        $view->setTemplate('layout/layout')
            ->setTerminal(true)
            ->addChild($content, 'content');
        return $view;
    }

    /**
     * Flashes a message to the user when a licence with a given id has no vehicles.
     *
     * @param int $licenceId
     * @throws Exception
     */
    protected function flashIfLicenceHasNoVehicles(int $licenceId)
    {
        $licence = $this->licenceRepository->findOneById($licenceId);
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
     * @throws Exception
     */
    protected function transferVehicles(int $currentLicenceId, array $vehicleIds, LicenceDTO $destinationLicence)
    {
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

    /**
     * Resolves any selected vehicle ids from a user's session.
     *
     * @return array<int>
     * @throws VehicleSelectionEmptyException
     */
    protected function resolveVehicleIdsFromSession()
    {
        $vehicleIds = $this->session->getVrms();
        if (empty($vehicleIds)) {
            throw new VehicleSelectionEmptyException();
        }

        // @todo move the following to the session class
        $parsedVehicleIds = [];
        foreach ($vehicleIds as $vehicleId) {
            $parsedVehicleIds[] = (int) $vehicleId;
        }
        return $parsedVehicleIds;
    }

    /**
     * Resolves the id of the requested destination licence for any vehicles that are to be transferred.
     *
     * @return LicenceDTO
     * @throws DestinationLicenceNotFoundWithIdException
     * @throws DestinationLicenceNotSetException
     */
    protected function resolveDestinationLicence(): LicenceDTO
    {
        $destinationLicenceId = $this->session->getDestinationLicenceId();
        if (null === $destinationLicenceId) {
            throw new DestinationLicenceNotSetException();
        }
        try {
            $destinationLicence = $this->licenceRepository->findOneById($destinationLicenceId);
        } catch (LicenceNotFoundWithIdException $exception) {
            throw new DestinationLicenceNotFoundWithIdException($destinationLicenceId);
        }
        return $destinationLicence;
    }
}
