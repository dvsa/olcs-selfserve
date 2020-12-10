<?php

namespace Olcs\Action\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Controller\DelegatesDispatchingInterface;
use Olcs\Controller\DelegatesPluginsInterface;
use Olcs\Controller\InteractsWithViewsTrait;
use Olcs\Form\Model\Form\Vehicle\VehicleConfirmationForm;
use Olcs\Repository\Licence\LicenceRepository;
use Olcs\Repository\Licence\Vehicle\LicenceVehicleRepository;
use Olcs\Session\LicenceVehicleManagement;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Http\Response;
use Laminas\Http\Request;
use Common\Form\Form;

abstract class TransferVehicleConfirmationAction implements DelegatesDispatchingInterface, DelegatesPluginsInterface
{
    use InteractsWithViewsTrait;

    protected const ROUTE_TRANSFER_INDEX = 'licence/vehicle/transfer/GET';
    protected const ROUTE_SWITCHBOARD = 'licence/vehicle/GET';

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
     * @var Url
     */
    protected $urlPlugin;

    /**
     * @var Redirect
     */
    protected $redirectPlugin;

    /**
     * @param FlashMessengerHelperService $flashMessenger
     * @param TranslationHelperService $translationService
     * @param LicenceVehicleManagement $session
     * @param FormHelperService $formService
     * @param LicenceRepository $licenceRepository
     * @param LicenceVehicleRepository $licenceVehicleRepository
     * @param Url $urlPlugin
     * @param Redirect $redirectPlugin
     */
    public function __construct(
        FlashMessengerHelperService $flashMessenger,
        TranslationHelperService $translationService,
        LicenceVehicleManagement $session,
        FormHelperService $formService,
        LicenceRepository $licenceRepository,
        LicenceVehicleRepository $licenceVehicleRepository,
        Url $urlPlugin,
        Redirect $redirectPlugin
    )
    {
        $this->flashMessenger = $flashMessenger;
        $this->translator = $translationService;
        $this->session = $session;
        $this->formService = $formService;
        $this->licenceRepository = $licenceRepository;
        $this->licenceVehicleRepository = $licenceVehicleRepository;
        $this->urlPlugin = $urlPlugin;
        $this->redirectPlugin = $redirectPlugin;
    }

    /**
     * @return array
     */
    public function getDelegatedPlugins(): array
    {
        return [$this->urlPlugin, $this->redirectPlugin];
    }

    /**
     * Creates a response to redirect to the transfer vehicles index page with an error message.
     *
     * @param int $licenceId
     * @param string $errorMessage
     * @return Response
     */
    protected function newRedirectToTransferIndexWithError(int $licenceId, string $errorMessage)
    {
        $this->flashMessenger->addErrorMessage($errorMessage);
        return $this->newRedirectToTransferIndex($licenceId);
    }

    /**
     * Creates a response to redirect to the transfer vehicles index page.
     *
     * @param int $licenceId
     * @return Response
     */
    protected function newRedirectToTransferIndex(int $licenceId)
    {
        return $this->redirectPlugin->toRoute(static::ROUTE_TRANSFER_INDEX, ['licence' => $licenceId]);
    }

    /**
     * Creates a response to redirect to the switchboard index page.
     *
     * @param int $licenceId
     * @return Response
     */
    protected function newRedirectToSwitchboard(int $licenceId)
    {
        return $this->redirectPlugin->toRoute(static::ROUTE_SWITCHBOARD, ['licence' => $licenceId]);
    }

    /**
     * Creates the form element for a controller.
     *
     * @param string $className
     * @param Request $request
     * @return Form
     */
    protected function createForm(string $className, Request $request)
    {
        $form = $this->formService->createForm($className, true, false);
        $this->formService->setFormActionFromRequest($form, $request);
        if ($request->isPost()) {
            $form->setData((array) $request->getPost());
        }

        $confirmationFieldMessages = $this->session->pullConfirmationFieldMessages();
        if (! empty($confirmationFieldMessages)) {

            // Populate confirmation field messages from session
            $confirmationField = $form
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_NAME);
            $confirmationField->setMessages($confirmationFieldMessages);
        }

        return $form;
    }
}
