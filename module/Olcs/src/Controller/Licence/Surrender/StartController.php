<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Surrender\Create;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Laminas\Http\Response;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\FlashMessenger;

class StartController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $templateConfig = [
        'default' => 'licence/surrender-index'
    ];

    protected $dataSourceConfig = [
        'index' => DataSourceConfig::LICENCE
    ];

    protected $formConfig = [
        'index' => [
            'startForm' => [
                'formClass' => \Olcs\Form\Model\Form\Surrender\Start::class
            ]
        ]
    ];

    private $translateService;

    public function onDispatch(MvcEvent $e)
    {
        $this->translateService = $this->getServiceLocator()->get('Helper\Translation');
        return parent::onDispatch($e);
    }

    /**
     * IndexAction
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        $licence = $this->data['licence'];
        return $this->getView($licence, $this->translateService);
    }

    /**
     * startAction
     *
     * @return \Laminas\View\Model\ViewModel | Response
     */
    public function startAction()
    {
        $licNo = $this->params('licence');
        $hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');

        try {
            $response = $this->handleCommand(Create::create(['id' => $licNo]));
            if ($response->isOk()) {
                $result = $response->getResult();
                if (!empty($result)) {
                    return $this->redirect()->toRoute(
                        'licence/surrender/review-contact-details/GET',
                        [
                            'licence' => $licNo,
                        ]
                    );
                }
            }
        } catch (AccessDeniedException $e) {
            $message = $this->translateService->translate('licence.surrender.already.applied');
            $hlpFlashMsgr->addInfoMessage($message);
        } catch (\Exception $e) {
            $hlpFlashMsgr->addUnknownError();
        }

        $this->redirect()->refresh();
    }

    private function getGvData()
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.gv',
            'cancelBus' => ['']
        ];
    }

    private function getPsvData($translateService)
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.psv',
            'cancelBus' => [$translateService->translate('licence.surrender.start.cancel.bus')]
        ];
    }

    /**
     * getView
     *
     * @param Licence    $licence
     * @param Translator $translateService
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function getView($licence, $translateService): \Laminas\View\Model\ViewModel
    {
        $view = $this->genericView();

        switch ($licence['goodsOrPsv']['id']) {
            case 'lcat_gv':
                $view->setVariables($this->getGvData());
                break;
            case 'lcat_psv':
                $view->setVariables($this->getPsvData($translateService));
                break;
            default:
                break;
        }

        $view->setVariable('licNo', $licence['licNo']);
        $view->setVariable('body', 'markup-licence-surrender-start');
        $view->setVariable('backUrl', $this->url()->fromRoute('lva-licence', ['licence' => $licence['id']]));
        $view->setVariable('startForm', $this->form);

        return $view;
    }
}
