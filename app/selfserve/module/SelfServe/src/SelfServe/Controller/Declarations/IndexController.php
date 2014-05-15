<?php

/**
 * Declarations Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Controller\Declarations;

use SelfServe\Controller\AbstractApplicationController;
use Zend\View\Model\ViewModel;

/**
 * Declarations Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class IndexController extends AbstractApplicationController
{

    protected $applicationData = [];

    /**
     * Set the current section upon construction
     *
     */
    public function __construct()
    {
        $this->setCurrentSection('declarations');
    }

    /**
     * Default index action; show full review summary
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->generateView('index');
    }

    /**
     * Display a simple, chromeless review form. Suitable for printing
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function simpleAction()
    {
        return $this->generateView('simple', 'layout/simple');
    }

    /**
     * Our complete action; a no-op so far...
     *
     * @return void
     */
    public function completeAction()
    {

    }

    protected function generateView($template, $layoutTemplate = null)
    {
        $applicationId = $this->getApplicationId();

        // collect completion status
        $completionStatus = $this->makeRestCall(
            'ApplicationCompletion',
            'GET',
            ['application_id' => $applicationId]
        );

        if ($completionStatus === null) {
            return $this->notFoundAction();
        }

        $form = $this->generateForm('review', null);

        $this->cleanForm($form);

        $form->setData($this->mapEntitiesToForm());

        $view = new ViewModel(
            [
                'completionStatus' => $completionStatus['Results'][0],
                'applicationId' => $applicationId,
                'form' => $form
            ]
        );
        $view->setTemplate('self-serve/declarations/' . $template);

        if ($layoutTemplate !== null) {
            $layout = $this->layout();
            $layout->setTemplate($layoutTemplate);
        }

        return $view;
    }

    /**
     * Map all gathered entity data to its corresponding form input data
     *
     * @return array
     */
    protected function mapEntitiesToForm()
    {
        $data = $this->getApplicationData();

        $formData = [
            'operator_location' => [
                'operator_location' => $data['licence']['niFlag'] ? 'ni' : 'uk',
            ],
            'operator-type' => [
                'operator-type' => $data['licence']['goodsOrPsv'],
            ]
        ];
        if ($data['licence']['goodsOrPsv'] === 'psv') {
            $formData['licence-type-psv'] = [
                'licence-type-psv' => $data['licence']['licenceType'],
            ];
        } else {
            $formData['licence-type'] = [
                'licence_type' => $data['licence']['licenceType'],
            ];
        }
        return $formData;

    }

    /**
     * Remove any unwanted data from our form object
     *
     * @param \Zend\Form\Form $form - the form object
     *
     * @return void
     */
    protected function cleanForm($form)
    {
        $data = $this->getApplicationData();

        // it's simpler to post-process the form to remove
        // the operator type rather than try and make the
        // original config too clever
        if ($data['licence']['niFlag']) {
            $form->remove('operator-type');
        }

        // likewise, depending on our licence type we want
        // to hide one of the licence-type fieldsets
        $remove = $data['licence']['goodsOrPsv'] === 'goods' ? '-psv' : '';
        $form->remove('licence-type' . $remove);
    }

    /**
     * Fetch the user's entire application data so far
     *
     * @return array
     */
    protected function getApplicationData()
    {
        if (count($this->applicationData) === 0) {
            // using an associative array allows the data room to
            // expand when the review page displays information from
            // more sections than just licence
            $this->applicationData = [
                'licence' => $this->getLicenceEntity()
            ];
        }
        return $this->applicationData;
    }
}
