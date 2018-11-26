<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCabotage;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtLicence;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;

class ConfirmChangeController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_LICENCE,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_CONFIRM_CHANGE_LICENCE,
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.change-licence.browser.title',
            'question' => 'permits.page.change-licence.question',
            'bulletList' => [
                'title' => 'permits.page.change-licence.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-licence-change'
            ]
        ]
    ];

    protected $postConfig = [
        'default' => [
            'command' => UpdateEcmtLicence::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];
}
