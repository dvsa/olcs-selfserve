<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\CreateEcmtPermitApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;

class LicenceController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'add' => DataSourceConfig::PERMIT_APP_ADD_LICENCE,
        'question' => DataSourceConfig::PERMIT_APP_LICENCE
    ];

    protected $conditionalDisplayConfig = [
        'add' => ConditionalDisplayConfig::PERMIT_APP_CAN_APPLY,
        'question' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $formConfig = [
        'add' => FormConfig::FORM_ADD_LICENCE,
        'question' => FormConfig::FORM_LICENCE,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'add' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'data' => [
                'question' => 'permits.page.licence.question',
                'newApplication' => true
            ]
        ],
        'question' => [
            'browserTitle' => 'permits.page.licence.browser.title',
            'data' => [
                'question' => 'permits.page.licence.question',
            ]
        ]
    ];

    protected $postConfig = [
        'add' => [
            'command' => CreateEcmtPermitApplication::class,
            'params' => ParamsConfig::NEW_APPLICATION,
            'step' => EcmtSection::ROUTE_ECMT_EURO6,
        ],
        'question' => [
            'params' => ParamsConfig::CONFIRM_CHANGE,
            'step' => EcmtSection::ROUTE_CONFIRM_CHANGE,
        ]
    ];

    public function addAction()
    {
        return $this->genericAction();
    }
}
