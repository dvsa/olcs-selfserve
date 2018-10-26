<?php
namespace Permits\Controller;

use Zend\Http\Response as HttpResponse;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\EcmtSection;
use Dvsa\Olcs\Transfer\Command\Permits\DeclineEcmtPermits;
use Dvsa\Olcs\Transfer\Command\Permits\AcceptEcmtPermits;

class FeePartSuccessfulController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_WITH_FEES,
    ];

    protected $conditionalDisplayConfig = [
        'generic' => ConditionalDisplayConfig::PERMIT_APP_AWAITING_FEE,
    ];

    protected $formConfig = [
        'generic' => FormConfig::FORM_ACCEPT_AND_PAY,
    ];

    protected $templateConfig = [
        'generic' => 'permits/fee-part-successful',
    ];

    protected $postConfig = [
        'generic' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_PAYMENT_ACTION,
        ]
    ];

    public function handlePost()
    {
        if (isset($this->postParams['Submit']['DeclineButton'])) {
            return $this->nextStep(EcmtSection::ROUTE_ECMT_DECLINE_APPLICATION);
        }

        return parent::handlePost();
    }
}
