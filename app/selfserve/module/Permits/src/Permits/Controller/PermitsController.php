<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;

use Common\Controller\Traits\GenericReceipt;
use Common\Controller\Traits\StoredCardsTrait;
use Dvsa\Olcs\Transfer\Query\ContactDetail\CountrySelectList;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Common\Util\FlashMessengerTrait;

use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Dvsa\Olcs\Transfer\Query\Permits\ById;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitApplication;

use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCountries;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtPermitsRequired;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtTrips;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateInternationalJourney;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateSector;

use Common\RefData;

use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\EcmtSection;

use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitFees;
use Zend\View\Model\ViewModel;

class PermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    use ExternalControllerTrait;
    use GenericReceipt;
    use StoredCardsTrait;
    use FlashMessengerTrait;

    const ECMT_APPLICATION_FEE_PRODUCT_REFENCE = 'IRHP_GV_APP_ECMT';
    const ECMT_ISSUING_FEE_PRODUCT_REFENCE = 'IRHP_GV_ECMT_100_PERMIT_FEE';

    protected $applicationsTableName = 'dashboard-permit-application';
    protected $issuedTableName = 'dashboard-permits-issued';

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $currentMessages = [];

    /**
     * @todo This is just a placeholder, this will be implemented properly using system parameters in OLCS-20848
     *
     * @var array
     */
    protected $govUkReferrers = [];

    public function indexAction()
    {
        $eligibleForPermits = $this->isEligibleForPermits();

        $view = new ViewModel();
        if (!$eligibleForPermits) {
            if (!$this->referredFromGovUkPermits($this->getEvent())) {
                return $this->notFoundAction();
            }
            return $view;
        }

        $query = EcmtPermitApplication::create(
            [
                'order' => 'DESC',
                'organisation' => $this->getCurrentOrganisationId(),
                'statusIds' => [
                    RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                    RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                    RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                    RefData::PERMIT_APP_STATUS_FEE_PAID,
                    RefData::PERMIT_APP_STATUS_ISSUING,
                    RefData::PERMIT_APP_STATUS_VALID,
                ],
            ]
        );
        $response = $this->handleQuery($query);
        $data = $response->getResult();

        $applicationData = $issuedData = [];

        foreach ($data['results'] as $item) {
            if ($item['status']['id'] === RefData::PERMIT_APP_STATUS_VALID) {
                $issuedData[] = $item;
            } else {
                $applicationData[] = $item;
            }
        }

        $table = $this->getServiceLocator()->get('Table');
        $issuedTable = $table->prepareTable($this->issuedTableName, $issuedData);
        $applicationsTable = $table->prepareTable($this->applicationsTableName, $applicationData);

        $view->setVariable('isEligible', $eligibleForPermits);
        $view->setVariable('issuedNo', count($issuedData));
        $view->setVariable('issuedTable', $issuedTable);
        $view->setVariable('applicationsNo', count($applicationData));
        $view->setVariable('applicationsTable', $applicationsTable);

        return $view;
    }

    public function restrictedCountriesAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('RestrictedCountriesForm');
        $setDefaultValues = true;

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            $setDefaultValues = false;

            if ($form->isValid()) {
                //EXTRA VALIDATION
                if (($data['Fields']['restrictedCountries'] == 1
                    && isset($data['Fields']['yesContent']['restrictedCountriesList']))
                    || ($data['Fields']['restrictedCountries'] == 0)) {
                    if ($data['Fields']['restrictedCountries'] == 0) {
                        $countryIds = [];
                    } else {
                        $countryIds = $data['Fields']['yesContent']['restrictedCountriesList'];
                    }

                    $command = UpdateEcmtCountries::create(['id' => $id, 'countryIds' => $countryIds]);
                    $this->handleCommand($command);
                    return $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_NO_OF_PERMITS);
                } else {
                    //conditional validation failed, restricted countries list should not be empty
                    $form->get('Fields')
                        ->get('yesContent')
                        ->get('restrictedCountriesList')
                        ->setMessages(['error.messages.restricted.countries.list']);
                }
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('restrictedCountries')
                    ->setMessages(['error.messages.restricted.countries']);
            }
        }

        // Read data
        $application = $this->getApplication($id);

        if ($setDefaultValues) {
            if (!is_null($application['hasRestrictedCountries'])) {
                $restrictedCountries = $application['hasRestrictedCountries'] == true ? 1 : 0;

                $form->get('Fields')
                    ->get('restrictedCountries')
                    ->setValue($restrictedCountries);
            }

            if (count($application['countrys']) > 0) {
                $form->get('Fields')
                    ->get('yesContent')
                    ->get('restrictedCountriesList')
                    ->setValue(array_column($application['countrys'], 'id'));
            }
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function tripsAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('TripsForm');
        $setDefaultValues = true;

        $data = $this->params()->fromPost();

        if (!empty($data)) {
            //Validate
            $form->setData($data);
            $setDefaultValues = false;

            if ($form->isValid()) {
                $command = UpdateEcmtTrips::create(['id' => $id, 'ecmtTrips' => $data['Fields']['tripsAbroad']]);
                $this->handleCommand($command);

                if ($data['Fields']['intensityWarning'] === 'no'
                    && $this->isHighIntensity($data['Fields']['permitsRequired'], $data['Fields']['tripsAbroad'])
                ) {
                    $form->get('Fields')->get('intensityWarning')->setValue('yes');
                } else {
                    return $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY);
                }
            }
        }

        $application = $this->getApplication($id);

        if ($setDefaultValues) {
            $existing = [
                'Fields' => [
                    'permitsRequired' => $application['permitsRequired'],
                    'tripsAbroad' => $application['trips'],
                    'intensityWarning' => 'no',
                ]
            ];
            $form->setData($existing);
        }

        return array(
            'form' => $form,
            'ref' => $application['applicationRef'],
            'id' => $id,
            'isNI' => $this->isNi($application)
        );
    }

    // Understand this is a computer property but added to avoid a round-trip to API for simple arithmetic operation.
    protected function isHighIntensity($permitsRequired, $tripsAbroad)
    {
        return !empty($permitsRequired) ? ($tripsAbroad / $permitsRequired) > 100 : false;
    }

    public function internationalJourneyAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('InternationalJourneyForm');

        $setDefaultValues = true;

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);
            $setDefaultValues = false;

            if ($form->isValid()) {
                $commandData = [
                    'id' => $id,
                    'internationalJourney' => $data['Fields']['InternationalJourney'],
                ];
                $command = UpdateInternationalJourney::create($commandData);
                $this->handleCommand($command);

                if ($data['Fields']['InternationalJourney'] === RefData::ECMT_APP_JOURNEY_OVER_90
                    && $data['Fields']['intensityWarning'] === 'no'
                ) {
                    $form->get('Fields')->get('intensityWarning')->setValue('yes');
                } else {
                    return $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_SECTORS);
                }
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('InternationalJourney')
                    ->setMessages(['error.messages.international-journey']);
            }
        }

        $application = $this->getApplication($id);

        if ($setDefaultValues) {
            $form->get('Fields')->get('InternationalJourney')->setValue($application['internationalJourneys']);
            $form->get('Fields')->get('intensityWarning')->setValue('no');
        }

        return array(
            'form' => $form,
            'id' => $id,
            'ref' => $application['applicationRef'],
            'trafficAreaId' => $application['licence']['trafficArea']['id']
        );
    }

    public function sectorAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('SpecialistHaulageForm');

        $setDefaultValues = true;

        $data = $this->params()->fromPost();

        if (is_array($data) && array_key_exists('Submit', $data)) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $command = UpdateSector::create(['id' => $id, 'sector' => $data['Fields']['SectorList']]);
                $this->handleCommand($command);

                return $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_CHECK_ANSWERS);
            } else {
                //Custom Error Message
                $form->get('Fields')
                    ->get('SectorList')
                    ->setMessages(['error.messages.sector.list']);

                $setDefaultValues = false;
            }
        }

        // Read data
        $application = $this->getApplication($id);

        if ($setDefaultValues && isset($application) && isset($application['sectors'])) {
            //Format results from DB before setting values on form
            $selectedValue = $application['sectors']['id'];

            $form->get('Fields')
                ->get('SectorList')
                ->setValue($selectedValue);
        }

        return array('form' => $form, 'id' => $id, 'ref' => $application['applicationRef']);
    }

    public function permitsRequiredAction()
    {
        $id = $this->params()->fromRoute('id', -1);

        //Create form from annotations
        $form = $this->getForm('PermitsRequiredForm');

        $setDefaultValues = true;

        $data = $this->params()->fromPost();

        if (!empty($data)) {
            //Validate
            $form->setData($data);

            if ($form->isValid()) {
                $command = UpdateEcmtPermitsRequired::create(
                    [
                        'id' => $id,
                        'permitsRequired' => $data['Fields']['permitsRequired']
                    ]
                );
                $this->handleCommand($command);

                return $this->handleSaveAndReturnStep($data, EcmtSection::ROUTE_ECMT_TRIPS);
            } else {
                $setDefaultValues = false;
            }
        }

        $application = $this->getApplication($id);
        $numberOfVehicles = $application['licence']['totAuthVehicles'];

        if ($setDefaultValues) {
            $existing = [
                'Fields' => [
                    'permitsRequired' => $application['permitsRequired'],
                    'numVehicles' => $numberOfVehicles,
                ]
            ];
            $form->setData($existing);
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $totalVehicles = $translationHelper->translateReplace(
            'permits.form.permits-required.hint',
            [$numberOfVehicles]
        );
        $form->get('Fields')->get('permitsRequired')->setOption('hint', $totalVehicles);

        $ecmtPermitFees = $this->getEcmtPermitFees();
        $ecmtApplicationFee = $ecmtPermitFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];

        return array(
            'form' => $form,
            'id' => $id,
            'ecmtApplicationFee' => $ecmtApplicationFee,
            'ref' => $application['applicationRef']
        );
    }

    public function ecmtGuidanceAction()
    {
        $query = CountrySelectList::create(['isEcmtState' => 1]);
        $response = $this->handleQuery($query);
        $ecmtCountries = $response->getResult();

        // Get Fee Data
        $ecmtPermitFees = $this->getEcmtPermitFees();
        $ecmtApplicationFee = $ecmtPermitFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];
        $ecmtIssuingFee = $ecmtPermitFees['fee'][$this::ECMT_ISSUING_FEE_PRODUCT_REFENCE]['fixedValue'];

        $view = new ViewModel();
        $view->setVariable('ecmtCountries', $ecmtCountries['results']);
        $view->setVariable('applicationFee', $ecmtApplicationFee);
        $view->setVariable('issueFee', $ecmtIssuingFee);
        return $view;
    }

    /**
     * Page displayed when from the Permit Dashboard
     * the user clicks the Reference of an application
     * in status 'Under Consideration'.
     *
     * From this page the user may or may not be given the
     * opportunity to withdraw the application.
     *
     */
    public function underConsiderationAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $application = $this->getApplication($id);

        if (!$application['isUnderConsideration']) {
            return $this->conditionalDisplayNotMet();
        }

        $ecmtPermitFees = $this->getEcmtPermitFees();
        $ecmtApplicationFee = $ecmtPermitFees['fee'][$this::ECMT_APPLICATION_FEE_PRODUCT_REFENCE]['fixedValue'];
        $ecmtApplicationFeeTotal = $ecmtApplicationFee * $application['permitsRequired'];

        /**
         * @todo status view helper and table config shouldn't be in the controller
         * @var \Common\View\Helper\Status $statusHelper
         */
        $statusHelper = $this->getServiceLocator()->get('ViewHelperManager')->get('status');

        $tableData = array(
            'results' => array(
                0 => array(
                    'applicationDetailsTitle' => 'permits.page.ecmt.consideration.application.status',
                    'applicationDetailsAnswer' => $statusHelper->__invoke($application['status'])
                ),
                1 => array(
                    'applicationDetailsTitle' => 'permits.page.ecmt.consideration.permit.type',
                    'applicationDetailsAnswer' => $application['permitType']['description']
                ),
                2 => array(
                    'applicationDetailsTitle' => 'permits.page.ecmt.consideration.reference.number',
                    'applicationDetailsAnswer' => $application['applicationRef']
                ),
                3 => array(
                    'applicationDetailsTitle' => 'permits.page.ecmt.consideration.application.date',
                    'applicationDetailsAnswer' => date(\DATE_FORMAT, strtotime($application['dateReceived']))
                ),
                4 => array(
                    'applicationDetailsTitle' => 'permits.page.ecmt.consideration.permits.required',
                    'applicationDetailsAnswer' => $application['permitsRequired']
                ),
                5 => array(
                    'applicationDetailsTitle' => 'permits.page.ecmt.consideration.application.fee',
                    'applicationDetailsAnswer' => '£' . $ecmtApplicationFeeTotal
                )
            )
        );

        /** @var \Common\Service\Table\TableBuilder $table */
        $table = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable('under-consideration', $tableData);

        $view = new ViewModel();
        $view->setVariable('application', $application);
        $view->setVariable('table', $table);

        return $view;
    }


    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function paymentAction()
    {
        $id = $this->params()->fromRoute('id', -1);
        $redirectUrl = $this->url()->fromRoute('permits/payment-result', ['id' => $id], ['force_canonical' => true]);

        $dtoData = [
            'cpmsRedirectUrl' => $redirectUrl,
            'ecmtPermitApplicationId' => $id,
            'paymentMethod' => RefData::FEE_PAYMENT_METHOD_CARD_ONLINE
        ];
        $dto = PayOutstandingFees::create($dtoData);
        $response = $this->handleCommand($dto);

        $messages = $response->getResult()['messages'];

        $translateHelper = $this->getServiceLocator()->get('Helper\Translation');
        $errorMessage = '';
        foreach ($messages as $message) {
            if (is_array($message) && array_key_exists(RefData::ERR_WAIT, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.15sec');
                break;
            } elseif (is_array($message) && array_key_exists(RefData::ERR_NO_FEES, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.feepaid');
                break;
            }
        }
        if ($errorMessage !== '') {
            $this->addErrorMessage($errorMessage);
            return $this->redirect()
                ->toRoute(EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $id]);
        }

        if (!$response->isOk()) {
            $this->addErrorMessage('feeNotPaidError');
            return $this->redirect()
                ->toRoute(EcmtSection::ROUTE_APPLICATION_OVERVIEW, ['id' => $id]);
        }

        // Look up the new payment in order to get the redirect data
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();

        $view = new ViewModel(
            [
                'gateway' => $payment['gatewayUrl'],
                'data' => [
                    'receipt_reference' => $payment['reference']
                ]
            ]
        );
        // render the gateway redirect
        $view->setTemplate('cpms/payment');
        return $this->render($view);
    }

    /**
     * Attach messages to display in the current response
     *
     * @return void
     */
    protected function attachCurrentMessages()
    {
        foreach ($this->currentMessages as $namespace => $messages) {
            foreach ($messages as $message) {
                $this->addMessage($message, $namespace);
            }
        }
    }


    /**
     * Whether the organisation is eligible for permits
     *
     * @return bool
     */
    private function isEligibleForPermits(): bool
    {
        $query = MyAccount::create([]);
        $response = $this->handleQuery($query)->getResult();

        return $response['eligibleForPermits'];
    }

    /**
     * Check whether the referrer is the gov.uk permits page
     *
     * @param MvcEvent $e
     *
     * @return bool
     */
    private function referredFromGovUkPermits(MvcEvent $e): bool
    {
        /**
         * @var HttpRequest      $request
         * @var HttpReferer|bool $referer
         */
        $request = $e->getRequest();
        $referer = $request->getHeader('referer');

        if (!$referer instanceof HttpReferer) {
            return false;
        }

        return in_array($referer->getUri(), $this->govUkReferrers);
    }

    /**
     * Returns an application entry by id
     *
     * @param number $id application id
     *
     * @return array
     */
    private function getApplication($id)
    {
        $query = ById::create(['id' => $id]);
        $response = $this->handleQuery($query);

        return $response->getResult();
    }

    /**
     * Returns Issuing application fees
     *
     * @return array
     */
    private function getEcmtPermitFees()
    {
        $query = EcmtPermitFees::create(
            [
                'productReferences' => [
                    self::ECMT_APPLICATION_FEE_PRODUCT_REFENCE,
                    self::ECMT_ISSUING_FEE_PRODUCT_REFENCE
                ]
            ]
        );
        $response = $this->handleQuery($query);
        return $response->getResult();
    }
}