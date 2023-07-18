<?php

namespace Olcs\Controller\Cookie;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Data\Mapper\MapperManager;

class DetailsController extends AbstractSelfserveController
{
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    protected $templateConfig = [
        'default' => 'pages/cookie/details',
    ];
}
