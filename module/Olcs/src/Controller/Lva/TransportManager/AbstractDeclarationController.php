<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\FeatureToggle;
use \Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\GetGovUkAccountRedirect;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\TransportManagerApplicationTrait;
use \Laminas\View\Model\ViewModel as LaminasViewModel;

abstract class AbstractDeclarationController extends AbstractController
{
    use ExternalControllerTrait,
        TransportManagerApplicationTrait;

    protected $declarationMarkup;

    /**
     * @var TransportManagerApplication
     */
    protected $tma;

    /**
     * Index action for the lva-transport_manager/tm_declaration and lva-transport_manager/declaration routes
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function indexAction(): LaminasViewModel
    {
        if ($this->getRequest()->isPost()) {
            if ($this->params()->fromPost('content')['isDigitallySigned'] === 'Y') {
                $this->digitalSignatureAction();
            } else {
                $this->physicalSignatureAction();
            }
        }
        return $this->renderDeclarationPage();
    }

    /**
     * @param array $tma
     *
     * @return LaminasViewModel
     */
    private function renderDeclarationPage(): LaminasViewModel
    {
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');

        $params = [
            'content' => $translationHelper->translateReplace(
                $this->declarationMarkup,
                $this->getTranslatedDeclarationMarkupParams($translationHelper)
            ),
            'tmFullName' => $this->getTmName(),
            'backText' => 'common.link.back.label',
            'backLink' => $this->getBackLink()
        ];

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('TransportManagerApplicationDeclaration');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        $this->alterDeclarationForm($form);

        $this->getServiceLocator()->get('Script')->loadFiles(['tm-lva-declaration']);

        $hasGovUkAccountError = $this->getFlashMessenger()->getContainer()->offsetExists('govUkAccountError');
        if ($hasGovUkAccountError) {
            $form->setMessages([
                'content' => [
                    'isDigitallySigned' => ['undertakings-sign-declaration-again']
                ],
            ]);
            $form->setOption('formErrorsParagraph', 'undertakings-govuk-account-generic-error');
        }

        $layout = $this->render('transport-manager-application.declaration', $form, $params);
        /* @var $layout \Laminas\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-action');

        return $layout;
    }

    /**
     * Return an array of parameters to be used as included as translateReplace inputs to the declaration markup
     *
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    protected function getTranslatedDeclarationMarkupParams(TranslationHelperService $translator)
    {
        return [];
    }

    protected function digitalSignatureAction()
    {
        $role = $this->getSignAsRole();
        // this will be either RefData::TMA_SIGN_AS_TM || RefData::TMA_SIGN_AS_TM_OP
        // isOwner will disambiguate later.
        $routeParams = ['lva'=>$this->lva, 'role'=>$role, 'applicationId'=>$this->tma['application']['id'], 'transportManagerApplicationId'=>$this->tma['id']];
        $featureEnabled = $this->handleQuery(IsEnabledQry::create(['ids' => [FeatureToggle::GOVUK_ACCOUNT]]))->getResult()['isEnabled'];
        if (!$featureEnabled) {
            return $this->redirect()->toRoute(
                'verify/transport-manager',
                $routeParams
            );
        }

        $returnUrl = $this->url()->fromRoute(
            'lva-' . $this->lva . '/transport_manager_confirmation',
            [
                'child_id' => $this->tma['id'],
                'application' => $this->tma['application']['id'],
                'action' => 'index'
            ], [], true
        );

        $urlResult = $this->handleCommand(GetGovUkAccountRedirect::create([
            'journey' => RefData::JOURNEY_TM_APPLICATION,
            'id' => $this->tma['id'],
            'role' => $role,
            'returnUrl' => $returnUrl,
            'returnUrlOnError' => $this->url()->fromRoute(null, [], [], true),
        ]));
        if (!$urlResult->isOk()) {
            throw new \Exception('GetGovUkAccountRedirect command returned non-OK', $urlResult->getStatusCode());
        }
        return $this->redirect()->toUrl($urlResult->getResult()['messages'][0]);
    }

    /**
     * Action for when the operator chooses to physically sign the transport manager application
     *
     * @param array $tma
     *
     * @return \Laminas\Http\Response
     */
    private function physicalSignatureAction()
    {
        $response = $this->handlePhysicalSignatureCommand();

        if ($response->isOk()) {
            return $this->redirect()->toRoute(
                "lva-" . $this->returnApplicationOrVariation() . "/transport_manager_details",
                [
                    'child_id' => $this->tma["id"],
                    'application' => $this->tma["application"]["id"]
                ]
            );
        } else {
            $this->flashMessenger()->addErrorMessage('unknown-error');
        }
    }

    abstract protected function handlePhysicalSignatureCommand(): \Common\Service\Cqrs\Response;

    abstract protected function getSubmitActionLabel(): string;

    abstract protected function getBackLink(): string;

    abstract protected function getSignAsRole(): string;

    /**
     * Alter declaration form
     *
     * @param Form $form
     *
     * @return void
     */
    protected function alterDeclarationForm(Form $form): void
    {
        $label = $this->getSubmitActionLabel();

        $form->get('form-actions')->get('submit')->setLabel($label);

        if ($this->tma['disableSignatures']) {
            $form->remove('content');
        }
    }
}
