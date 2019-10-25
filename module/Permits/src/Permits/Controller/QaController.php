<?php

namespace Permits\Controller;

use Common\Controller\AbstractOlcsController;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationStep;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationStep;
use Olcs\Service\Qa\FormProvider;
use Olcs\Service\Qa\TemplateVarsGenerator;
use Permits\View\Helper\EcmtSection;
use Permits\View\Helper\IrhpApplicationSection;
use Zend\View\Helper\HeadTitle;
use Zend\View\Model\ViewModel;

class QaController extends AbstractOlcsController
{
    const FIELDSET_DATA_PREFIX = 'fieldset';

    /** @var FormProvider */
    private $formProvider;

    /** @var TemplateVarsGenerator */
    private $templateVarsGenerator;

    /** @var HeadTitle */
    private $headTitle;

    /**
     * Create service instance
     *
     * @param FormProvider $formProvider
     * @param TemplateVarsGenerator $templateVarsGenerator
     * @param HeadTitle
     *
     * @return QaController
     */
    public function __construct(
        FormProvider $formProvider,
        TemplateVarsGenerator $templateVarsGenerator,
        HeadTitle $headTitle
    ) {
        $this->formProvider = $formProvider;
        $this->templateVarsGenerator = $templateVarsGenerator;
        $this->headTitle = $headTitle;
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $routeParams = $this->params()->fromRoute();

        $query = ApplicationStep::create(
            [
                'id' => $routeParams['id'],
                'slug' => $routeParams['slug'],
            ]
        );

        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            throw new RuntimeException('Non-success response received from backend');
        }

        $result = $response->getResult();
        $form = $this->formProvider->get($result['applicationStep']);
        $showErrorInBrowserTitle = false;

        if ($this->request->isPost()) {
            $postParams = $this->params()->fromPost();
            $form->setData($postParams);

            if ($form->isValid()) {
                $command = SubmitApplicationStep::create(
                    [
                        'id' => $routeParams['id'],
                        'slug' => $routeParams['slug'],
                        'postData' => $postParams
                    ]
                );

                $this->handleCommand($command);

                if (isset($postParams['Submit']['SaveAndReturnButton'])) {
                    return $this->redirect()->toRoute(
                        IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                        [
                            'id' => $routeParams['id']
                        ]
                    );
                }

                return $this->redirect()->toRoute(
                    $this->event->getRouteMatch()->getMatchedRouteName(),
                    [
                        'id' => $routeParams['id'],
                        'slug' => $result['nextStepSlug'],
                    ]
                );
            }

            $showErrorInBrowserTitle = true;

            // transfer data normalised by input filter back into form, don't touch anything apart from the
            // Q&A fieldset content to avoid unwanted form breakage
            $normalisedData = $form->getData();
            foreach ($normalisedData['qa'] as $key => $value) {
                $postParams['qa'][$key] = $value;
            }

            $form->setDataForRedisplay($postParams);
        }

        $templateVars = array_merge(
            $this->templateVarsGenerator->generate($result['questionText']),
            [
                'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                'cancelUrl' => EcmtSection::ROUTE_PERMITS,
                'application' => [
                    'applicationRef' => $result['applicationReference']
                ],
            ]
        );

        $this->headTitle->setSeparator(' - ');
        $this->headTitle->prepend($result['title']);

        if ($showErrorInBrowserTitle) {
            $this->headTitle->set($this->headTitle->renderTitle());
            $this->headTitle->setSeparator(': ');
            $this->headTitle->prepend('permits.application.browser.title.error');
            $this->headTitle->set($this->headTitle->renderTitle());
            $this->headTitle->setSeparator(' - ');
        }

        $view = new ViewModel();
        $view->setVariable('data', $templateVars);
        $view->setVariable('form', $form);

        $view->setTemplate('permits/single-question');

        return $view;
    }
}