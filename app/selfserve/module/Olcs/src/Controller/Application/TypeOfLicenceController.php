<?php

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractApplicationController
{
    /**
     * Type of licence section
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);

            if ($form->isValid()) {

                $licenceId = $this->getLicenceId($applicationId);

                $data = $this->formatDataForSave($data);

                $data['id'] = $licenceId;

                $this->getEntityService('Licence')->save($data);

                return $this->goToOverview($applicationId);
            }
        } else {
            $licenceId = $this->getLicenceId($applicationId);
            $typeOfLicenceData = $this->getEntityService('Licence')->getTypeOfLicenceData($licenceId);

            $form->setData(
                array(
                    'version' => $typeOfLicenceData['version'],
                    'type-of-licence' => array(
                        'operator-location' => $typeOfLicenceData['niFlag'],
                        'operator-type' => $typeOfLicenceData['goodsOrPsv'],
                        'licence-type' => $typeOfLicenceData['licenceType']
                    )
                )
            );
        }

        return $this->getSectionView($form);
    }

    /**
     * Create application action
     */
    public function createApplicationAction()
    {
        $request = $this->getRequest();

        $form = $this->getTypeOfLicenceForm();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);

            if ($form->isValid()) {

                $organisation = $this->getCurrentOrganisation();
                $ids = $this->getEntityService('Application')->createNew($organisation['id']);

                $data = $this->formatDataForSave($data);

                $data['id'] = $ids['licence'];

                $this->getEntityService('Licence')->save($data);

                return $this->goToOverview($ids['application']);
            }
        }

        return $this->getSectionView($form);
    }

    /**
     * Format data for save
     *
     * @param array $data
     * @return array
     */
    private function formatDataForSave($data)
    {
        return array(
            'version' => $data['version'],
            'niFlag' => $data['type-of-licence']['operator-location'],
            'goodsOrPsv' => $data['type-of-licence']['operator-type'],
            'licenceType' => $data['type-of-licence']['licence-type']
        );
    }

    /**
     * Get type of licence form
     *
     * @return \Zend\Form\Form
     */
    private function getTypeOfLicenceForm()
    {
        return $this->getHelperService('FormHelper')->createForm('Lva\TypeOfLicence');
    }

    /**
     * Get section view
     *
     * @param \Zend\Form\Form $form
     * @return Section
     */
    private function getSectionView(Form $form)
    {
        // @TODO in a custom view model instead?
        $this->getServiceLocator()
            ->get('Script')
            ->loadFile('type-of-licence');

        return new Section(
            [
                'title' => 'Type of licence',
                'form' => $form
            ]
        );
    }
}
