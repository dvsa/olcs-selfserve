<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\Lva\Traits\TypeOfLicenceTrait;

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractApplicationController
{
    use TypeOfLicenceTrait;

    /**
     * Type of licence section
     */
    public function indexAction()
    {
        // @TODO Need to ensure the application is NOT a variation

        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        if ($this->isButtonPressed('cancel')) {
            return $this->goToOverview($applicationId);
        }

        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $typeOfLicenceData = $this->getTypeOfLicenceData();

            $data = array(
                'version' => $typeOfLicenceData['version'],
                'type-of-licence' => array(
                    'operator-location' => $typeOfLicenceData['niFlag'],
                    'operator-type' => $typeOfLicenceData['goodsOrPsv'],
                    'licence-type' => $typeOfLicenceData['licenceType']
                )
            );
        }

        $form->setData($data);

        if ($request->isPost() && $form->isValid()) {

            $licenceId = $this->getLicenceId($applicationId);

            $data = $this->formatDataForSave($data);

            $data['id'] = $licenceId;

            $this->getEntityService('Licence')->save($data);

            $this->updateCompletionStatuses($applicationId);

            if ($this->isButtonPressed('saveAndContinue')) {
                return $this->goToNextSection('type_of_licence');
            }

            return $this->goToOverview($applicationId);
        }

        return $this->getSectionView($form);
    }

    /**
     * Create application action
     */
    public function createApplicationAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRoute('dashboard');
        }

        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();
        $form->get('form-actions')
            ->remove('saveAndContinue')
            ->get('save')->setLabel('continue.button');

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            if ($form->isValid()) {

                $organisation = $this->getCurrentOrganisation();
                $ids = $this->getEntityService('Application')->createNew($organisation['id']);

                $data = $this->formatDataForSave($data);

                $data['id'] = $ids['licence'];
                $data['version'] = 1;

                $this->getEntityService('Licence')->save($data);

                $this->updateCompletionStatuses($ids['application']);

                return $this->goToOverview($ids['application']);
            }
        }

        return $this->getSectionView($form);
    }
}
