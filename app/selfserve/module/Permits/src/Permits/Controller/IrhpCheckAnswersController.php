<?php
namespace Permits\Controller;

use Common\Preference\Language;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCheckAnswers;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\AnswersSummary;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\IrhpApplicationSection;

class IrhpCheckAnswersController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_CHECK_ANSWERS,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_CHECK_ANSWERS,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_IRHP_CHECK_ANSWERS,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-check-answers'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'browserTitle' => 'permits.page.check-answers.browser.title',
            'title' => 'permits.page.check-answers.title',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => UpdateCheckAnswers::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_DECLARATION,
            'saveAndReturnStep' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];

    public function retrieveData()
    {
        parent::retrieveData();

        $irhpApplicationId = $this->data[IrhpAppDataSource::DATA_KEY]['id'];

        $languagePreference = $this->getServiceLocator()
            ->get('LanguagePreference')
            ->getPreference();

        $translateToWelsh = $languagePreference == Language::OPTION_CY ? 'Y' : 'N';

        $answersSummaryParams = [
            'id' => $irhpApplicationId,
            'translateToWelsh' => $translateToWelsh
        ];

        $response = $this->handleQuery(
            AnswersSummary::create($answersSummaryParams)
        );

        $result = $response->getResult();

        $this->data['rows'] = $result['rows'];
    }
}
