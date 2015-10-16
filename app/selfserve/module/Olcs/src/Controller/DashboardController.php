<?php

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;
use Common\Service\Entity\UserEntityService;
use Dvsa\Olcs\Transfer\Query\Organisation\Dashboard as DashboardQry;

/**
 * Dashboard Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DashboardController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait,
        Lva\Traits\DashboardNavigationTrait;

    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        if ($this->isGranted(UserEntityService::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(UserEntityService::PERMISSION_SELFSERVE_LVA)) {
            $view = $this->transportManagerDashboardView();
        } else {
            $view = $this->standardDashboardView();
        }

        return $view;
    }

    /**
     * Get the Standard Dashboard view
     *
     * @return Dashboard
     */
    protected function standardDashboardView()
    {
        $organisationId = $this->getCurrentOrganisationId();

        // retrieve data
        $query = DashboardQry::create(['id' => $organisationId]);
        $response = $this->handleQuery($query);
        $dashboardData = $response->getResult()['dashboard'];

        // build tables
        $tables = $this->getServiceLocator()->get('DashboardProcessingService')->getTables($dashboardData);

        // setup view
        $view = new \Zend\View\Model\ViewModel($tables);
        $view->setTemplate('dashboard');

        // populate the navigation tabs with correct counts
        $this->populateTabCounts(
            $dashboardData['feeCount'],
            $dashboardData['correspondenceCount']
        );

        return $view;
    }

    /**
     * Get the Dashboard view for a Transport Manager
     */
    protected function transportManagerDashboardView()
    {
        $userId = $this->currentUser()->getUserData()['id'];

        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetList::create(['user' => $userId])
        );
        $results = $response->getResult()['results'];

        // flatten the array
        $data = $this->getServiceLocator()->get('DataMapper\DashboardTmApplications')->map($results);

        // create table
        $table = $this->getServiceLocator()->get('Table')->buildTable('dashboard-tm-applications', $data);

        // setup view
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('dashboard-tm');
        $view->setVariable('applicationsTable', $table);

        return $view;
    }
}
