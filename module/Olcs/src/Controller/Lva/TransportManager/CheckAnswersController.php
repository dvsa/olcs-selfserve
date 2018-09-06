<?php

namespace OLCS\Controller\Lva\TransportManager;

use OLCS\Command\TransportManagerApplication\Submit;
use Common\Controller\Lva\AbstractTransportManagersController;
use Common\Data\Mapper\Lva\TransportManagerApplication;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

class CheckAnswersController extends AbstractTransportManagersController
{

    use ApplicationControllerTrait;

    public function indexAction()
    {
        $transportManagerApplicationId = $this->params("application");

        $transportManagerApplication = $this->getTransportManagerApplication($transportManagerApplicationId);
        $translator = $this->serviceLocator->get('Helper\Translation');

        $checkAnswersHint = $translator->translate('lva.section.transport-manager-check-answers-hint');
        $title = 'check_answers';
        $defaultParams = [
            'content' => $checkAnswersHint,
            'tmFullName' => $this->getTmName($transportManagerApplication),
            'backLink' => $this->url()->fromRoute(
                "dashboard",
                [],
                [],
                false
            ),
            'backText' => 'transport-manager-save-return',

        ];
        $form = $this->getConfirmationForm($transportManagerApplicationId);
        $sections = TransportManagerApplication::mapForSections($transportManagerApplication);
        $params = array_merge(["sections" => $sections], $defaultParams);
        /* @var $layout \Zend\View\Model\ViewModel */
        $layout = $this->render($title, $form, $params);
        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-checkAnswers');

        return $layout;
    }

    /**
     * confirmAction
     */
    public function confirmAction()
    {

        $transportManagerApplicationId = $this->params("application");
        if ($this->getRequest()->isPost()) {
            $response = $this->handleCommand(
                Submit::create(['id' =>$transportManagerApplicationId])
            );

            $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
            if ($response->isOk()) {
                $flashMessenger->addSuccessMessage('lva-tm-details-submit-success');
                //redirect to declaration at this point.
                exit("Decalarion page -> OLCS-19791");
            } else {
                $flashMessenger->addErrorMessage('unknown-error');
            }
        }
    }

    /**
     * getConfirmationForm
     *
     * @param $transportManagerApplicationId
     *
     * @return \Common\Form\Form
     */
    private function getConfirmationForm(int $transportManagerApplicationId): \Common\Form\Form
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('GenericConfirmation');
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'lva-transport_manager/check_answers',
                ['application' => $transportManagerApplicationId]
            ) . 'confirm'
        );
        $submitLabel = 'Confirm and continue';
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }
}
