<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Data\Mapper\MapperManager;

class MaxPermittedReachedForStockController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::PERMIT_APP_MAX_PERMITTED_REACHED_FOR_STOCK,
    ];

    protected $conditionalDisplayConfig = [
        'generic' => ConditionalDisplayConfig::PERMIT_APP_CAN_SHOW_MAX_PERMITTED_REACHED_FOR_STOCK,
    ];

    protected $templateConfig = [
        'generic' => 'permits/max-permitted-reached-for-stock'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.max-permitted-reached-for-stock.browser.title',
        ]
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }
}
