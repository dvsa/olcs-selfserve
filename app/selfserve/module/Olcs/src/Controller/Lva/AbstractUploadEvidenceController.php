<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Traits\GenericUpload;
use Common\Form\Form;
use Dvsa\Olcs\Transfer\Query\Application\UploadEvidence;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Abstract Upload Evidence Controller
 */
abstract class AbstractUploadEvidenceController extends AbstractController
{
    use GenericUpload,
        ApplicationControllerTrait;

    protected $location = 'external';

    /**
     * Data from API
     * @var array
     */
    private $data;

    /**
     * Index action
     *
     * @return \Common\View\Model\Section
     */
    public function indexAction()
    {
        $form = $this->getForm();

        $request = $this->getRequest();
        if ($request->isPost() && $request->getPost('saveAndContinue') !== null) {
            $form->setData((array) $request->getPost());

            if ($form->isValid()) {
                $dtoData = array_merge(
                    \Common\Data\Mapper\Lva\UploadEvidence::mapFromForm($form->getData()),
                    ['id' => $this->getIdentifier()]
                );
                $dtoData['financialEvidence'] = $this->shouldShowFinancialEvidence();

                $result = $this->handleCommand(
                    \Dvsa\Olcs\Transfer\Command\Application\UploadEvidence::create($dtoData)
                );
                if ($result->isOk()) {
                    return $this->redirect()->toRoute(
                        'lva-' . $this->lva . '/submission-summary',
                        ['application' => $this->getIdentifier()]
                    );
                }
                $this->addErrorMessage('unknown-error');
            }
        }

        return $this->render('upload-evidence', $form);
    }

    /**
     * Get the form
     *
     * @return Form
     */
    private function getForm()
    {
        /** @var Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('Lva\UploadEvidence');

        if ($this->shouldShowFinancialEvidence()) {
            $this->processFiles(
                $form,
                'financialEvidence->files',
                [$this, 'financialEvidenceProcessFileUpload'],
                [$this, 'deleteFile'],
                [$this, 'financialEvidenceLoadFileUpload']
            );
        } else {
            $form->remove('financialEvidence');
        }

        if ($this->shouldShowOperatingCentre()) {
            $data = $this->getData();
            $form->get('operatingCentres')->setCount(count($data['operatingCentres']));
            \Common\Data\Mapper\Lva\UploadEvidence::mapFromResultForm($data, $form);

            for ($i = 0; $i < count($data['operatingCentres']); $i++) {
                // process files for each operating centre
                $this->operatingCentreId = $data['operatingCentres'][$i]['operatingCentre']['id'];
                $this->processFiles(
                    $form,
                    'operatingCentres->'. (string)$i .'->file',
                    [$this, 'operatingCentreProcessFileUpload'],
                    [$this, 'deleteFile'],
                    [$this, 'operatingCentreLoadFileUpload']
                );
            }
        } elseif ($form->has('operatingCentres')) {
            $form->remove('operatingCentres');
        }
        return $form;
    }

    /**
     * Get list of uploaded document for an operating centre
     *
     * @return array
     */
    public function operatingCentreLoadFileUpload()
    {
        $data = $this->getData();
        foreach ($data['operatingCentres'] as $aocData) {
            if ($aocData['operatingCentre']['id'] === $this->operatingCentreId) {
                $documents = $aocData['operatingCentre']['adDocuments'];
                return array_filter($documents, function ($document) {
                    return $document['postSubmissionUpload'];
                });
            }
        }

        return [];
    }

    /**
     * Process a file upload to an operating centre
     *
     * @param array $file Uploaded file data
     *
     * @return void
     */
    public function operatingCentreProcessFileUpload($file)
    {
        $data = [
            'description' => 'Advertisement',
            'category' => \Common\Category::CATEGORY_APPLICATION,
            'subCategory' => \Common\Category::DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL,
            'isExternal'  => $this->isExternal(),
            'licence' => $this->getLicenceId(),
            'application' => $this->getIdentifier(),
            'operatingCentre' => $this->operatingCentreId,
            'isPostSubmissionUpload' => true
        ];

        $this->uploadFile($file, $data);

        // force refresh of data
        $this->getData(true);
    }

    /**
     * Get financial evidence data
     *
     * @param bool $forceLoad Force load of data, eg don't use the cached version
     *
     * @return array
     */
    private function getFinancialEvidenceData($forceLoad = false)
    {
        $data = $this->getData($forceLoad);
        return $data['financialEvidence'];
    }

    /**
     * Get API data
     *
     * @param bool $forceLoad Force load of data, eg don't use the cached version
     *
     * @return array
     */
    private function getData($forceLoad = false)
    {
        if ($this->data !== null && !$forceLoad) {
            return $this->data;
        }

        $response = $this->handleQuery(UploadEvidence::create(['id' => $this->getIdentifier()]));
        if (!$response->isOk()) {
            throw new \RuntimeException('Error calling query UploadEvidence');
        }
        $this->data = $response->getResult();

        return $this->data;
    }

    /**
     * Should the Operating centre section be shown on the form
     *
     * @return bool
     */
    private function shouldShowOperatingCentre()
    {
        $application = $this->getApplicationData($this->getIdentifier());
        return $application['goodsOrPsv']['id'] == 'lcat_psv' ? false : true;
    }

    /**
     * Should the financial evidence section be shown on the form
     *
     * @return bool
     */
    private function shouldShowFinancialEvidence()
    {
        $financialEvidenceData = $this->getFinancialEvidenceData();
        return $financialEvidenceData['canAdd'] === true;
    }

    /**
     * Process/upload a new financial evidence document
     *
     * @param array $file Data for the uploaded file
     *
     * @return void
     */
    public function financialEvidenceProcessFileUpload($file)
    {
        $applicationData = $this->getApplicationData($this->getIdentifier());
        $data = [
            'application' => $applicationData['id'],
            'description' => $file['name'],
            'category'    => \Common\Category::CATEGORY_APPLICATION,
            'subCategory' => \Common\Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL,
            'licence'     => $applicationData['licence']['id'],
            'isExternal'  => true,
            'isPostSubmissionUpload' => true
        ];

        $this->uploadFile($file, $data);

        // force refresh of data
        $this->getFinancialEvidenceData(true);
    }

    /**
     * Get list of financial evidence documents
     *
     * @return array
     */
    public function financialEvidenceLoadFileUpload()
    {
        $financialEvidenceData = $this->getFinancialEvidenceData();
        return $financialEvidenceData['documents'];
    }
}
