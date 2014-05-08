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
    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        // collect completion status
        $completionStatus = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));

        // render the view
        $view = new ViewModel(array('completionStatus' => $completionStatus['Results'][0],
                                            'applicationId' => $applicationId));
        $view->setTemplate('self-serve/declarations/index');

        return $view;
    }

    public function completeAction()
    {

    }
}
