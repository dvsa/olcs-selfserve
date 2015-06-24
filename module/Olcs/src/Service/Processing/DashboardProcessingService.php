<?php

/**
 * Dashboard presentation logic
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Olcs\Service\Processing;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Common\RefData;

/**
 * Dashboard presentation logic
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Restrict the types of licence we display
     */
    private $displayLicenceStatus = array(
        RefData::LICENCE_STATUS_VALID,
        RefData::LICENCE_STATUS_CURTAILED,
        RefData::LICENCE_STATUS_SUSPENDED
    );

    /**
     * Restrict the types of applications / variations we display
     */
    private $displayApplicationStatus = array(
        RefData::APPLICATION_STATUS_UNDER_CONSIDERATION,
        RefData::APPLICATION_STATUS_GRANTED,
        RefData::APPLICATION_STATUS_NOT_SUBMITTED
    );

    /**
     * Get the three tables for display on the dashboard
     *
     * @param array $data organisation data
     * @return array ['licences', 'applications', 'variations'] all containing a table
     */
    public function getTables($data)
    {
        $applications = array();
        $variations = array();
        $licences = array();

        if (isset($data['licences']) && !empty($data['licences'])) {

            foreach ($data['licences'] as $licence) {

                if (in_array($licence['status']['id'], $this->displayLicenceStatus)) {
                    $licence['status'] = $licence['status']['id'];
                    $licence['type'] = $licence['licenceType']['id'];
                    $licences[$licence['id']] = $licence;
                }

                foreach ($licence['applications'] as $application) {
                    $newRow = $application;
                    $newRow['licNo'] = $licence['licNo'];
                    $newRow['status'] = (string)$application['status']['id'];

                    if (in_array($newRow['status'], $this->displayApplicationStatus)) {
                        if ($application['isVariation']) {
                            $variations[$newRow['id']] = $newRow;
                        } else {
                            $applications[$newRow['id']] = $newRow;
                        }
                    }
                }
            }

            krsort($licences);
            krsort($variations);
            krsort($applications);
        }

        $tableService = $this->getServiceLocator()->get('Table');

        return [
            'licences' => $tableService->buildTable('dashboard-licences', $licences),
            'variations' => $tableService->buildTable('dashboard-variations', $variations),
            'applications' => $tableService->buildTable('dashboard-applications', $applications),
        ];
    }
}
