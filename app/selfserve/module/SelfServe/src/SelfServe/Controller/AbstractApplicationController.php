<?php

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller;

use Common\Controller\FormJourneyActionController;

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationController extends FormJourneyActionController
{
    /**
     * Holds the applicationId
     */
    protected $applicationId;

    /**
     * Sub sections
     *
     * @var array
     */
    protected $subSections = array();

    /**
     * Check if a button was pressed
     *
     * @param string $button
     */
    protected function isButtonPressed($button)
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            if (isset($data['form-actions'][$button])) {

                return true;
            }
        }

        return false;
    }

    /**
     * Render the layout with sub sections

     * @param object $view
     * @param string $current
     */
    protected function renderLayoutWithSubSections($view, $current = '')
    {
        $subSections = $this->getSubSections();

        foreach ($subSections as $key => &$details) {

            $details['active'] = false;
            if ($key == $current) {
                $details['active'] = true;
            }
        }

        $layout = $this->getViewModel(
            array(
                'subSections' => $subSections
            )
        );

        $layout->setTemplate('self-serve/layout/withSubSections');

        $layout->addChild($view, 'main');

        return $layout;
    }

    /**
     * Getter for subSections
     *
     * @return array
     */
    protected function getSubSections()
    {
        return $this->subSections;
    }

    /**
     * Setter for subSections
     *
     * @param array $subSections
     */
    protected function setSubSections($subSections = array())
    {
        $this->subSections = $subSections;
    }

    /**
     * Return the applicationId
     *
     * @return int
     */
    protected function getApplicationId()
    {
        if (empty($this->applicationId)) {
            $this->applicationId = $this->params()->fromRoute('applicationId');
        }

        return $this->applicationId;
    }

    /**
     * Return a variable from route
     *
     * @param string $name
     * @return mixed
     */
    protected function fromRoute($name)
    {
        return $this->params()->fromRoute($name);
    }
}
