<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Command\Application\UpdateLgvDeclaration;

/**
 * External Abstract LGV Undertakings Controller
 */
abstract class AbstractLgvUndertakingsController extends AbstractController
{
    protected $location = 'external';

    /**
     * Check for redirect (before the controller action is executed)
     *
     * @param int $lvaId Lva id
     *
     * @return null|\Laminas\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        $appData = $this->getApplicationData($lvaId);

        if (empty($appData['canMakeLgvDeclaration'])) {
            return $this->notFoundAction();
        }

        return parent::checkForRedirect($lvaId);
    }

    /**
     * Index action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function indexAction()
    {
        $form = $this->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array) $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $response = $this->save($form->getData());

                if ($response->isOk()) {
                    return $this->completeSection('type_of_licence');
                }
            }
        } else {
            $applicationData = $this->getApplicationData($this->getApplicationId());
            $data = $this->formatDataForForm($applicationData);
            $form->setData($data);
        }

        return $this->render(
            'lgv-undertakings',
            $form,
            [
                'backUrlOverride' => [
                    'url' => $this->url()->fromRoute('lva-' . $this->lva . '/type_of_licence', [], [], true)
                ]
            ]
        );
    }

    /**
     * Get the form
     *
     * @return Form
     */
    private function getForm(): Form
    {
        /** @var \Olcs\FormService\Form\Lva\LgvUndertakings $formManagerService */
        $formManagerService = $this->getServiceLocator()->get('FormServiceManager')
            ->get('lva-lgv-undertakings');

        /** @var Form $form */
        $form = $formManagerService->getForm();

        return $form;
    }

    /**
     * Save the form data
     *
     * @param array $formData form data
     *
     * @return Response
     */
    private function save(array $formData): Response
    {
        $data = [
            'id' => $this->getApplicationId(),
            'version' => $formData['lgvUndertakings']['version'],
        ];

        $result = $this->handleCommand(
            UpdateLgvDeclaration::create($data)
        );

        if (!$result->isOk()) {
            $this->addErrorMessage('unknown-error');
        }

        return $result;
    }

    /**
     * Format data for form
     *
     * @param array $applicationData application data
     *
     * @return array
     */
    private function formatDataForForm(array $applicationData): array
    {
        $output = [
            'lgvUndertakings' => [
                'id' => $applicationData['id'],
                'version' => $applicationData['version'],
                'lgvDeclarationConfirmation' => $applicationData['lgvDeclarationConfirmation'],
            ]
        ];

        return $output;
    }
}
