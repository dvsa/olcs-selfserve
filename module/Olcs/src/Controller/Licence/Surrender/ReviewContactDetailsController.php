<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\ReviewContactDetails;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Olcs\Service\Surrender\SurrenderStateService;

class ReviewContactDetailsController extends AbstractSurrenderController
{

    public function indexAction()
    {
        return $this->renderView($this->getViewVariables());
    }

    public function postAction()
    {
        if ($this->markContactsComplete()) {
            return $this->redirect()->toRoute($this->getNextStep(), [], [], true);
        }

        $this->hlpFlashMsgr->addUnknownError();

        return $this->renderView($this->getViewVariables());
    }

    protected function getViewVariables(): array
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        return [
            'title' => 'licence.surrender.review_contact_details.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => 'licence.surrender.review_contact_details.content',
            'note' => 'licence.surrender.review_contact_details.note',
            'form' => $this->getConfirmationForm($translator),
            'backLink' => $this->getLink('lva-licence'),
            'sections' => ReviewContactDetails::makeSections($this->data['surrender']['licence'], $this->url(), $translator),
        ];
    }

    private function getConfirmationForm(TranslationHelperService $translator): \Common\Form\Form
    {
        /* @var $form \Common\Form\GenericConfirmation */
        $form = $this->hlpForm->createForm('GenericConfirmation');
        $submitLabel = $translator->translate('approve-details');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }

    protected function markContactsComplete(): bool
    {
        return $this->updateSurrender(RefData::SURRENDER_STATUS_CONTACTS_COMPLETE);
    }

    protected function getNextStep()
    {
        $surrenderStateService = new SurrenderStateService();
        $surrenderStateService->setSurrenderData($this->data['surrender']);
        if ($surrenderStateService->getDiscsOnLicence() > 0) {
            return 'licence/surrender/current-discs/GET';
        }

        return 'licence/surrender/operator-licence/GET';
    }
}
