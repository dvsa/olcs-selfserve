<?php
namespace Permits\Controller;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\AvailableCountries;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\IrhpApplicationSection;
use Laminas\Mvc\MvcEvent;

class IrhpApplicationCountryController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_COUNTRIES,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_READY_FOR_COUNTRIES,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_COUNTRIES,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'default' => [
            'browserTitle' => 'permits.page.bilateral.countries.browser.title',
            'question' => 'permits.page.bilateral.countries.question',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => UpdateCountries::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'saveAndReturnStep' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];

    /**
     * Fix the issue whereby the country checkboxes selected in the form are not ticked on the postback where none
     * were ticked on form submission
     *
     * @param MvcEvent $e event
     *
     * @return array|mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        if ($this->request->isPost()) {
            $post = $this->request->getPost();

            $values = $post->get('fields');
            if (is_null($values)) {
                $values = [
                    'countries' => []
                ];
                $post->set('fields', $values);
            }
        }

        return parent::onDispatch($e);
    }

    public function mergeTemplateVars()
    {
        if (!isset($this->queryParams['fromOverview'])) {
            // overwrite default backUri
            // to be removed by a future ticket when/if a generic solution is found
            $this->templateVarsConfig['default']['backUri'] = IrhpApplicationSection::ROUTE_PERMITS;
            $this->templateVarsConfig['default']['backLabel'] = 'common.link.back-to-permits.label';
        }

        parent::mergeTemplateVars();
    }

    /**
     * Extend method to allow the selected checkboxes to be determined by a list of country codes specified on the
     * querystring. Used by back button and cancel button on countries confirmation page
     */
    public function retrieveForms()
    {
        if (empty($this->postParams) &&
            isset($this->queryParams['countries']) &&
            is_string($this->queryParams['countries'])
        ) {
            $countryCodeIndexMap = [];
            foreach ($this->data[AvailableCountries::DATA_KEY]['countries'] as $index => $country) {
                $countryCode = $country['id'];
                $countryCodeIndexMap[$countryCode] = $index;
            }

            $this->data[IrhpApplication::DATA_KEY]['countrys'] = [];
            $selectedCountryCodes = explode(',', $this->queryParams['countries']);
            foreach ($selectedCountryCodes as $countryCode) {
                if (isset($countryCodeIndexMap[$countryCode])) {
                    $index = $countryCodeIndexMap[$countryCode];
                    $country = $this->data[AvailableCountries::DATA_KEY]['countries'][$index];
                    $this->data[IrhpApplication::DATA_KEY]['countrys'][] = $country;
                }
            }
        }

        parent::retrieveForms();
    }

    /**
     * Extend method to redirect to countries confirmation page when one or more of the currently selected countries
     * are unselected
     */
    public function handlePost()
    {
        if (isset($this->postParams['Submit']['CancelButton'])) {
            return $this->redirect()->toRoute(
                IrhpApplicationSection::ROUTE_CANCEL_APPLICATION,
                [],
                [
                    'query' => [
                        'fromCountries' => '1'
                    ]
                ],
                true
            );
        }

        if (isset($this->postParams['fields']['countries']) &&
            is_array($this->postParams['fields']['countries']) &&
            !empty($this->postParams['fields']['countries'])
        ) {
            $availableCountryCodes = array_column(
                $this->data[AvailableCountries::DATA_KEY]['countries'],
                'id'
            );
            $selectedCountryCodes = $this->postParams['fields']['countries'];
            $validatedSelectedCountryCodes = array_intersect($availableCountryCodes, $selectedCountryCodes);
            $linkedCountryCodes = array_column($this->data[IrhpApplication::DATA_KEY]['countrys'], 'id');
            $removedCountryCodes = array_diff($linkedCountryCodes, $validatedSelectedCountryCodes);

            if (!empty($removedCountryCodes)) {
                $selectedCountryCodesCsv = implode(',', $selectedCountryCodes);

                return $this->redirect()->toRoute(
                    IrhpApplicationSection::ROUTE_COUNTRIES_CONFIRMATION,
                    [],
                    [
                        'query' => [
                            'countries' => $selectedCountryCodesCsv
                        ]
                    ],
                    true
                );
            }
        }

        return parent::handlePost();
    }
}
