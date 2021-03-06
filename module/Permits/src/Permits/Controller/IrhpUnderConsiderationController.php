<?php

namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpUnderConsiderationController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_UNDER_CONSIDERATION,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_UNDER_CONSIDERATION,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-under-consideration'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'backUri' => IrhpApplicationSection::ROUTE_PERMITS,
            'browserTitle' => 'permits.irhp.under-consideration.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function retrieveData()
    {
        parent::retrieveData();

        $this->data = $this->getServiceLocator()
            ->get(IrhpApplicationFeeSummary::class)
            ->mapForDisplay($this->data);
    }
}
