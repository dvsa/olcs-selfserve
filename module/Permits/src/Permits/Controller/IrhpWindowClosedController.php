<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpWindowClosedController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-window-closed'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.irhp-window-closed.browser.title',
        ]
    ];
}
