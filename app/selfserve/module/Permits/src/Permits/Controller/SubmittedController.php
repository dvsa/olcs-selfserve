<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class SubmittedController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_UNDER_CONSIDERATION,
        'decline' => [],
        'fee-submitted' => []
    ];

    protected $templateConfig = [
        'generic' => 'permits/submitted',
        'decline' => 'permits/submitted',
        'fee-submitted' => 'permits/submitted'
    ];

    public function genericAction()
    {
        $ecmtApplicationId = $this->params()->fromRoute('id');
        $view = parent::genericAction();
        $view->setVariable('partialName', 'markup-ecmt-application-submitted');
        $view->setVariable('titleName', 'permits.application.submitted.title');
        $view->setVariable('mainName', 'permits.application.submitted.main');
        $view->setVariable('receiptUrl', $this->url()->fromRoute('permits/ecmt-print-receipt', ['id' => $ecmtApplicationId, 'reference' => $this->params()->fromQuery('receipt_reference')]));


        return $view;
    }

    public function feeSubmittedAction()
    {
        $view = parent::genericAction();
        $view->setVariable('partialName', 'markup-ecmt-application-fee-submitted');
        $view->setVariable('titleName', 'permits.application.fee.submitted.title');
        $view->setVariable('mainName', 'permits.application.fee.submitted.main');

        return $view;
    }

    public function declineAction()
    {
        $view = parent::genericAction();

        $view->setVariable('partialName', 'markup-ecmt-decline-submitted');
        $view->setVariable('titleName', 'permits.decline.submitted.title');
        $view->setVariable('mainName', 'permits.decline.submitted.main');

        return $view;
    }
}
