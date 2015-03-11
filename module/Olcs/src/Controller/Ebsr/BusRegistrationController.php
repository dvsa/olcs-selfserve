<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\AbstractActionController;
use Common\Exception\ResourceNotFoundException;

/**
 * Class BusRegVariationController
 */
class BusRegistrationController extends AbstractActionController
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $ebsrSubmissionDataService = $this->getEbsrSubmissionDataService();

        $busRegistrationList = $ebsrSubmissionDataService->fetchList();

        $busRegistrationTable = $tableBuilder->buildTable(
            'bus-registrations',
            $busRegistrationList,
            ['url' => $this->plugin('url')],
            false
        );

        return $this->getView(
            [
                'busRegistrationTable' => $busRegistrationTable,
            ]
        );
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $busRegDataService = $this->getBusRegDataService();

        $registrationDetails = $busRegDataService->fetchDetail($id);

        if (empty($registrationDetails)) {
            throw new ResourceNotFoundException('Bus registration could not be found');
        }

        $variationHistory = $busRegDataService->fetchVariationHistory($registrationDetails['routeNo']);

        $latestPublication = $this->getLatestPublicationByType(
            $registrationDetails['licence'],
            'N&P'
        );

        $registrationDetails['npRreferenceNo'] = $latestPublication['publicationNo'];
        $variationHistoryTable = $tableBuilder->buildTable(
            'bus-reg-variation-history',
            $variationHistory,
            ['url' => $this->plugin('url')],
            false
        );

        return $this->getView(
            [
                'registrationDetails' => $registrationDetails,
                'variationHistoryTable' => $variationHistoryTable
            ]
        );
    }

    /**
     * Returns the latest publication by type from a licence
     *
     * @param $licence
     * @param $type string
     * @return array|null
     */
    private function getLatestPublicationByType($licence, $type)
    {
        if (isset($licence['publicationLinks'][0]['publication'])) {
            usort(
                $licence['publicationLinks'],
                function ($a, $b) {
                    return strtotime($b['publication']['pubDate']) - strtotime($a['publication']['pubDate']);
                }
            );
            foreach ($licence['publicationLinks'] as $publicationLink) {
                if ($publicationLink['publication']['pubType'] == $type) {
                    return $publicationLink['publication'];
                }
            }
        }
        return null;
    }

    /**
     * @return \Olcs\Service\Data\BusReg
     */
    public function getBusRegDataService()
    {
        /** @var \Common\Service\Data\BusReg $dataService */
        $dataService = $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\BusReg');
        return $dataService;
    }

    /**
     * @return \Olcs\Service\Data\EbsrSubmission
     */
    public function getEbsrSubmissionDataService()
    {
        /** @var \Generic\Service\Data\EbsrSubmission $dataService */
        $dataService = $this->getServiceLocator()->get('DataServiceManager')->get('\Generic\Service\Data\EbsrSubmission');
        return $dataService;
    }
}
