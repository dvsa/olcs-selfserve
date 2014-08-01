<?php

/**
 * Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\ReviewDeclarations;

use Zend\View\Model\ViewModel;

/**
 * Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends ReviewDeclarationsController
{
    protected $validateForm = false;

    /**
     * Holds the data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => 'ALL',
        'children' => array(
            'licence' => array(),
            'documents' => array()
        )
    );

    /**
     * Summary sections
     *
     * @var array
     */
    private $summarySections = array(
        'TypeOfLicence/OperatorLocation',
        'TypeOfLicence/OperatorType',
        'TypeOfLicence/LicenceType',
        'PreviousHistory/FinancialHistory'
    );

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Alter the form
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $data = $this->loadCurrent();
        $options = array(
            'isPsv' => $this->isPsv(),
            'isReview' => true
        );

        foreach ($this->summarySections as $summarySection) {
            list($section, $subSection) = explode('/', $summarySection);

            $formName = $this->formatFormName('Application', $section, $subSection);
            $fieldsetName = $formName . '-1';

            if (!$this->isSectionAccessible($section, $subSection)) {
                $form->remove($fieldsetName);
            } else {
                $controller = '\SelfServe\Controller\Application\\' . $section . '\\' . $subSection . 'Controller';
                if (method_exists($controller, 'makeFormAlterations')) {
                    $newOptions = array_merge(
                        $options,
                        array(
                            'fieldset' => $fieldsetName,
                            'data'     => $data
                        )
                    );
                    $form = $controller::makeFormAlterations($form, $this, $newOptions);
                }
            }
        }
        return $form;
    }

    /**
     * Render the section form
     *
     * @return Response
     */
    public function simpleAction()
    {
        $this->isAction = false;

        $this->setRenderNavigation(false);
        $this->setLayout('layout/simple');

        $layout = $this->renderSection();

        if ($layout instanceof ViewModel) {
            $layout->setTerminal(true);
        }

        return $layout;
    }

    /**
     * Placeholder save method
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
    }

    /**
     * Process load
     *
     * @param array $loadData
     */
    protected function processLoad($loadData)
    {
        $data = array(
            'application_type-of-licence_operator-location-1' => array(
                'niFlag' => ($loadData['licence']['niFlag'] == 1 ? '1' : '0')
            ),
            'application_type-of-licence_operator-type-1' => array(
                'goodsOrPsv' => $loadData['licence']['goodsOrPsv']
            ),
            'application_type-of-licence_licence-type-1' => array(
                'licenceType' => $loadData['licence']['licenceType']
            ),
            'application_previous-history_financial-history-1' => $this->mapApplicationVariables(
                array(
                    'bankrupt',
                    'liquidation',
                    'receivership',
                    'administration',
                    'disqualified',
                    'insolvencyDetails',
                    'insolvencyConfirmation'
                ),
                $loadData
            )
        );

        return $data;
    }

    protected function mapApplicationVariables($map, $data)
    {
        $final = array();

        foreach ($map as $entry) {
            if (isset($data[$entry])) {
                $final[$entry] = $data[$entry];
            }
        }

        return $final;
    }
}
