<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\Application\AbstractTypeOfLicenceController;
use Common\FormService\FormServiceManager;
use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Laminas\Form\Form;
use Common\RefData;
use Common\View\Model\Section;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController
{
    use ApplicationControllerTrait;

    protected $location = 'external';
    protected $lva = 'application';

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Laminas\Form\Form $form
     * @return \Common\View\Model\Section
     */
    protected function renderCreateApplication($titleSuffix, Form $form = null)
    {
        return new Section(
            [
                'title' => 'lva.section.title.' . $titleSuffix, 'form' => $form,
                'stepX' => '1',
                // don't display any additional information like progress, app number, etc on create app page
                'lva' => ''
            ]
        );
    }

    /**
     * Create application action
     */
    public function createApplicationAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRouteAjax('dashboard');
        }

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        /** @var \Common\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence $tolFormManagerService */
        $tolFormManagerService = $this->getServiceLocator()->get(FormServiceManager::class)
            ->get('lva-application-type-of-licence');
        /** @var \Common\Form\Form $form */
        $form = $tolFormManagerService->getForm();

        $organisationData = $this->getOrganisation($this->getCurrentOrganisationId());
        if (isset($organisationData['allowedOperatorLocation'])) {
            $tolFormManagerService->setAndLockOperatorLocation($form, $organisationData['allowedOperatorLocation']);
        }

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            $tolFormManagerService->maybeAlterFormForNi($form);
            $tolFormManagerService->maybeAlterFormForGoodsStandardInternational($form);

            if ($form->isValid()) {
                $data = $form->getData();

                $typeOfLicenceData = $data['type-of-licence'];
                $licenceTypeData = $typeOfLicenceData['licence-type'];
                $operatorType = $typeOfLicenceData['operator-type'];
                $licenceType = $licenceTypeData['licence-type'];
                $vehicleType = null;
                $lgvDeclarationConfirmation = 0;

                if (isset($licenceTypeData['ltyp_siContent'])) {
                    $siContentData = $licenceTypeData['ltyp_siContent'];
                    $vehicleType = $siContentData['vehicle-type'];

                    if (isset($siContentData['lgv-declaration']['lgv-declaration-confirmation'])) {
                        $lgvDeclarationConfirmation = $siContentData['lgv-declaration']['lgv-declaration-confirmation'];
                    }
                }

                $dto = CreateApplication::create(
                    [
                        'organisation' => $this->getCurrentOrganisationId(),
                        'niFlag' => $this->getOperatorLocation($organisationData, $data),
                        'operatorType' => $operatorType,
                        'licenceType' => $licenceType,
                        'vehicleType' => $vehicleType,
                        'lgvDeclarationConfirmation' => $lgvDeclarationConfirmation
                    ]
                );

                $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

                /** @var \Common\Service\Cqrs\Response $response */
                $response = $this->getServiceLocator()->get('CommandService')->send($command);

                if ($response->isOk()) {
                    return $this->goToOverview($response->getResult()['id']['application']);
                }

                if ($response->isClientError()) {
                    $this->mapErrors($form, $response->getResult()['messages']);
                } else {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addErrorMessage('unknown-error');
                }
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('type-of-licence');

        return $this->renderCreateApplication('type_of_licence', $form);
    }

    /**
     * Get Organisation data
     *
     * @param int $id
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getOrganisation($id)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(['id' => $id])
        );
        if (!$response->isOk()) {
            throw new \RuntimeException(
                $this->getServiceLocator()->get('Helper\Translation')->translate('external.error-getting-organisation')
            );
        }
        return $response->getResult();
    }
}
