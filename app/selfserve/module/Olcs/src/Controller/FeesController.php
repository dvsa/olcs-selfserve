<?php

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;
use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentById;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePayment;
use Common\Controller\Traits\GenericReceipt;
use Dvsa\Olcs\Transfer\Query\Fee\Fee;
use Common\Controller\Traits\StoredCardsTrait;

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeesController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait,
        StoredCardsTrait,
        GenericReceipt;

    const PAYMENT_METHOD = RefData::FEE_PAYMENT_METHOD_CARD_ONLINE;

    private $disableCardPayments = false;

    /**
     * Fees index action
     */
    public function indexAction()
    {
        $response = $this->checkActionRedirect();
        if ($response) {
            return $response;
        }

        $organisationId = $this->getCurrentOrganisationId();

        $fees = $this->getOutstandingFeesForOrganisation($organisationId);

        $table = $this->getServiceLocator()->get('Table')
            ->buildTable('fees', $fees, [], false);

        if ($this->getDisableCardPayments()) {
            $table->removeAction('pay');
            $table->removeColumn('checkbox');
            $this->getServiceLocator()->get('Helper\Guidance')->append('selfserve-card-payments-disabled');
        }

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/fees/home');

        $this->getServiceLocator()->get('Script')->loadFile('dashboard-fees');

        return $view;
    }

    /**
     * Pay Fees action
     */
    public function payFeesAction()
    {
        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel') || $this->isButtonPressed('customCancel')) {
                return $this->redirectToIndex();
            }
            $storedCardReference = (is_array($this->getRequest()->getPost('storedCards')) &&
                $this->getRequest()->getPost('storedCards')['card'] != '0') ?
                $this->getRequest()->getPost('storedCards')['card'] :
                false;

            $feeIds = explode(',', $this->params('fee'));
            return $this->payOutstandingFees($feeIds, $storedCardReference);
        }

        $fees = $this->getFeesFromParams();

        if (empty($fees)) {
            $this->addErrorMessage('payment.error.feepaid');
            return $this->redirectToIndex();
        }

        /* @var $form \Common\Form\Form */
        $form = $this->getForm();
        $firstFee = reset($fees);
        $this->setupSelectStoredCards($form, $firstFee['feeType']['isNi']);

        if (count($fees) > 1) {
            $table = $this->getServiceLocator()->get('Table')
                ->buildTable('pay-fees', $fees, [], false);
            $view = new ViewModel(
                [
                    'table' => $table,
                    'form' => $form,
                    'hasContinuation' => $this->hasContinuationFee($fees),
                    'type' => 'fees'
                ]
            );
            $view->setTemplate('pages/fees/pay-multi');
        } else {
            $fee = array_shift($fees);
            $view = new ViewModel(
                [
                    'fee' => $fee,
                    'form' => $form,
                    'hasContinuation' => $fee['feeType']['feeType']['id'] == RefData::FEE_TYPE_CONT,
                    'type' => 'fees'
                ]
            );
            $view->setTemplate('pages/fees/pay-one');
        }

        if ($this->getDisableCardPayments()) {
            $form->get('form-actions')->remove('pay');
            $form->get('form-actions')->get('cancel')->setLabel('back-to-fees');
            $form->get('form-actions')->get('cancel')->setAttribute('class', 'action--tertiary large');
            $this->getServiceLocator()->get('Helper\Guidance')->append('selfserve-card-payments-disabled');
        }

        return $view;
    }

    public function handleResultAction()
    {
        $queryStringData = (array)$this->getRequest()->getQuery();

        $dtoData = [
            'reference' => $queryStringData['receipt_reference'],
            'cpmsData' => $queryStringData,
            'paymentMethod' => self::PAYMENT_METHOD,
        ];

        $response = $this->handleCommand(CompletePayment::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToIndex();
        }

        // check payment status and redirect accordingly
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentById::create(['id' => $paymentId]));
        $payment = $response->getResult();
        switch ($payment['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                return $this->redirectToReceipt($queryStringData['receipt_reference']);
            case RefData::TRANSACTION_STATUS_CANCELLED:
                break;
            case RefData::TRANSACTION_STATUS_FAILED:
            default:
                $this->addErrorMessage('payment-failed');
                break;
        }
        return $this->redirectToIndex();
    }

    public function receiptAction()
    {
        $paymentRef = $this->params()->fromRoute('reference');

        $viewData = $this->getReceiptData($paymentRef);

        $view = new ViewModel($viewData);
        $view->setTemplate('pages/fees/payment-success');
        return $view;
    }

    protected function getOutstandingFeeDataForOrganisation($organisationId)
    {
        $query = OutstandingFees::create(['id' => $organisationId, 'hideExpired' => true]);
        $response = $this->handleQuery($query);

        $this->disableCardPayments = $response->getResult()['disableCardPayments'];

        return $response->getResult();
    }

    protected function getOutstandingFeesForOrganisation($organisationId)
    {
        $result = $this->getOutstandingFeeDataForOrganisation($organisationId);
        return $result['outstandingFees'];
    }

    /**
     * Are Card payments disabled
     *
     * @return bool
     */
    protected function getDisableCardPayments()
    {
        return $this->disableCardPayments;
    }


    /**
     * Get fees by ID(s) from params, note these *must* be a subset of the
     * outstanding fees for the current organisation - any invalid IDs are
     * ignored
     */
    protected function getFeesFromParams()
    {
        $fees = [];

        $organisationId = $this->getCurrentOrganisationId();
        $outstandingFees = $this->getOutstandingFeesForOrganisation($organisationId);

        if (!empty($outstandingFees)) {
            $ids = explode(',', $this->params('fee'));
            foreach ($outstandingFees as $fee) {
                if (in_array($fee['id'], $ids)) {
                    $fees[] = $fee;
                }
            }
        }

        return $fees;
    }

    protected function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('FeePayment');
    }

    protected function checkActionRedirect()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();
            if (!isset($data['id']) || empty($data['id'])) {
                $this->addErrorMessage('fees.pay.error.please-select');
                return $this->redirectToIndex();
            }
            $params = [
                'fee' => implode(',', $data['id']),
            ];
            return $this->redirect()->toRoute('fees/pay', $params, null, true);
        }
    }

    protected function redirectToIndex()
    {
        return $this->redirect()->toRoute('fees');
    }

    protected function redirectToReceipt($reference)
    {
        return $this->redirect()->toRoute('fees/receipt', ['reference' => $reference]);
    }

    /**
     * Calls command to initiate payment and then redirects
     *
     * @param array $feeIds
     * @param string|false $storedCardReference A refernce to the stored card to use
     */
    protected function payOutstandingFees(array $feeIds, $storedCardReference = false)
    {
        $cpmsRedirectUrl = $this->getServiceLocator()->get('Helper\Url')
            ->fromRoute('fees/result', [], ['force_canonical' => true], true);

        $paymentMethod = self::PAYMENT_METHOD;
        $organisationId = $this->getCurrentOrganisationId();

        $dtoData = compact('cpmsRedirectUrl', 'feeIds', 'paymentMethod', 'organisationId', 'storedCardReference');
        $dto = PayOutstandingFees::create($dtoData);

        /** @var \Common\Service\Cqrs\Response $response */
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
            return $this->redirectToIndex();
        }

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToIndex();
        }

        // due to CQRS, we now need another request to look up the payment in
        // order to get the redirect data :-/
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentById::create(['id' => $paymentId]));
        $payment = $response->getResult();
        $view = new ViewModel(
            [
                'gateway' => $payment['gatewayUrl'],
                'data' => [
                    'receipt_reference' => $payment['reference']
                ]
            ]
        );
        $view->setTemplate('cpms/payment');

        return $this->render($view);
    }

    /**
     * Late fee action
     *
     * @return ViewModel
     */
    public function lateFeeAction()
    {
        $feeId = $this->params('fee');
        $response = $this->handleQuery(Fee::create(['id' => $feeId]));
        if (!$response->isOk()) {
            throw new ResourceNotFoundException('Fee not found');
        }
        $result = $response->getResult();
        $view = new ViewModel(
            ['licenceExpiryDate' => date('d F Y', strtotime($result['licenceExpiryDate']))]
        );
        $view->setTemplate('pages/fees/late');
        return $this->render($view);
    }
}
