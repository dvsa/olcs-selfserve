<?php

/**
 * External Abstract Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\Summary as Qry;

/**
 * External Abstract Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractSummaryController extends AbstractController
{
    protected $location = 'external';

    public function indexAction()
    {
        return $this->renderSummary($this->getParams(true));
    }

    public function postSubmitSummaryAction()
    {
        return $this->renderSummary($this->getParams());
    }

    public function renderSummary($params)
    {
        $view = new ViewModel($params);
        $view->setTemplate('pages/application-summary');

        return $this->render($view);
    }

    protected function getParams($justPaid = false)
    {
        $id = $this->getIdentifier();

        $dto = Qry::create(['id' => $id]);
        $response = $this->handleQuery($dto);
        $data = $response->getResult();

        $reference = $this->params()->fromRoute('reference') ?
            $this->params()->fromRoute('reference') : $data['reference'];
        return [
            'justPaid' => $justPaid,
            'lva' => $this->lva,
            'licence' => $data['licence']['licNo'],
            'application' => $data['id'],
            'canWithdraw' => ($data['status']['id'] === RefData::APPLICATION_STATUS_UNDER_CONSIDERATION),
            'status' => $data['status']['description'],
            'submittedDate' => $data['receivedDate'],
            'completionDate' => $data['targetCompletionDate'],
            'paymentRef' => $reference,
            'actions' => $data['actions'],
            'transportManagers' => $data['transportManagers'] ? $data['transportManagers'] : []
        ];
    }
}
