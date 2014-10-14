<?php

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractExternalController;
use Common\Controller\Traits\Lva\ApplicationControllerTrait;

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationController extends AbstractExternalController
{
    use ApplicationControllerTrait;

    /**
     * Holds the lva type
     *
     * @var string
     */
    protected $lva = 'application';

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->isApplicationNew($applicationId)) {
            return $this->notFoundAction();
        }

        return $this->checkForRedirect($applicationId);
    }

    /**
     * Check if the user has access to the application
     *
     * @NOTE We might want to consider caching this information within the session, to save making this request on each
     *  section
     *
     * @param int $applicationId
     * @return boolean
     */
    protected function checkAccess($applicationId)
    {
        $organisationId = $this->getCurrentOrganisationId();

        $doesBelong = $this->getServiceLocator()->get('Entity\Application')
            ->doesBelongToOrganisation($applicationId, $organisationId);

        if (!$doesBelong) {
            $this->addErrorMessage('application-no-access');
        }

        return $doesBelong;
    }

    /**
     * Get type of licence data
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());

        return $this->getServiceLocator()->get('Entity\Licence')->getTypeOfLicenceData($licenceId);
    }
}
