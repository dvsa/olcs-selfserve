<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Application;

use SelfServe\Controller\AbstractJourneyController;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractJourneyController
{
    /**
     * Holds the licenceDataBundle
     *
     * @var array
     */
    public static $licenceDataBundle = array(
        'children' => array(
            'licence' => array(
                'properties' => array(
                    'id',
                    'version',
                    'goodsOrPsv',
                    'niFlag',
                    'licenceType'
                )
            )
        )
    );

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Application';

    /**
     * Cache licence data requests
     *
     * @var array
     */
    private $licenceData = array();

    /**
     * Check if is psv
     *
     * @var boolean
     */
    protected $isPsv = null;

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        $completion = $this->getSectionCompletion();

        if (isset($completion['lastSection'])) {
            return $this->goToSection($completion['lastSection']);
        }

        return $this->goToFirstSection();
    }

    /**
     * Check if the vehicle safety section is enabled
     *
     * @return boolean
     */
    public function isVehicleSafetyEnabled()
    {
        if (!$this->isPsv()) {
            return true;
        }

        return ($this->getSectionStatus('OperatingCentres') == 'complete');
    }

    /**
     * Save the last section
     *
     * @param ViewModel $view
     * @return ViewModel
     */
    protected function preRender($view)
    {
        $this->saveLastSection();

        return parent::preRender($view);
    }

    /**
     * Save the last section
     */
    protected function saveLastSection()
    {
        // We use the full section completion as it gets cached and will be used again
        $completion = $this->getSectionCompletion();

        $data = array(
            'id' => $completion['id'],
            'version' => $completion['version'],
            'lastSection' => $this->getJourneyName() . '/' . $this->getSectionName() . '/' . $this->getSubSectionName()
        );

        $this->makeRestCall('ApplicationCompletion', 'PUT', $data);

        $completion['version'] ++;

        $this->setSectionCompletion($completion);
    }

    /**
     * Check if application is psv
     *
     * GetAccessKeys "should" always be called first so psv should be set
     *
     * @return boolean
     */
    protected function isPsv()
    {
        return $this->isPsv;
    }

    /**
     * Return an array of access keys
     *
     * @param boolean $force
     * @return array
     */
    protected function getAccessKeys($force = false)
    {
        if (empty($this->accessKeys) || $force) {
            $licence = $this->getLicenceData();

            if (empty($licence)) {
                return parent::getAccessKeys($force);
            }

            if (strtolower($licence['goodsOrPsv']) == 'psv') {
                $this->isPsv = true;
            } else {
                $this->isPsv = false;
            }

            $type = str_replace(' ', '-', strtolower($licence['licenceType']));

            if (strstr($type, 'standard')) {
                $type = 'standard';
            }

            $this->accessKeys = array(
                trim(strtolower($licence['goodsOrPsv']) . '-' . $type, '-')
            );

            if (isset($licence['niFlag']) && !is_null($licence['niFlag']) && $licence['niFlag'] !== '') {
                $this->accessKeys[] = ($licence['niFlag'] == 1 ? 'ni' : 'gb');
            }

            $sectionCompletion = $this->getSectionCompletion();

            if (isset($sectionCompletion['sectionPaymentSubmissionStatus'])
                && $sectionCompletion['sectionPaymentSubmissionStatus'] == 2) {

                $this->accessKeys[] = 'paid';
            } else {
                $this->accessKeys[] = 'unpaid';
            }
        }

        return $this->accessKeys;
    }

    /**
     * Get the licence data
     *
     * @return array
     */
    protected function getLicenceData()
    {
        if (empty($this->licenceData)) {

            $application = $this->makeRestCall(
                'Application',
                'GET',
                array('id' => $this->getIdentifier()),
                self::$licenceDataBundle
            );

            $this->licenceData = $application['licence'];
        }

        return $this->licenceData;
    }
}
