<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Data\Mapper\Licence\Surrender\ReviewContactDetails;
use Common\Service\Helper\TranslationHelperService;

class ReviewContactDetailsController extends AbstractSurrenderController implements ToggleAwareInterface
{

    public function indexAction()
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        $params = [
            'title' => 'licence.surrender.review_contact_details.title',
            'licNo' => $this->licence['licNo'],
            'content' => 'licence.surrender.review_contact_details.content',
            'note' => 'licence.surrender.review_contact_details.note',
            'form' => $this->getConfirmationForm(),
            'backLink' => $this->getBackLink('licence/surrender/start'),
            'sections' => ReviewContactDetails::makeSections($this->licence, $this->url(), $translator),
        ];

        return $this->renderView($params);
    }

    public function confirmAction()
    {
        // TODO: here we should change the status and redirect to next step
    }

    private function getConfirmationForm(): \Common\Form\Form
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        /* @var $form \Common\Form\GenericConfirmation */
        $form = $formHelper->createForm('GenericConfirmation');
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'licence/surrender/review-contact-details',
                [
                    'action' => 'confirm',
                    'licence' => $this->licenceId,
                    'surrender' => $this->surrenderId
                ]
            )
        );
        $submitLabel = $translator->translate('confirm-and-continue');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }

}
