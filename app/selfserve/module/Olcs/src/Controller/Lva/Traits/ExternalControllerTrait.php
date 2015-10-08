<?php

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\View\Model\Section;
use Dvsa\Olcs\Transfer\Query\User\UserSelfserve as UserQry;

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ExternalControllerTrait
{
    /**
     * Redirect back to overview
     */
    protected function handleCancelRedirect($lvaId)
    {
        return $this->goToOverview($lvaId);
    }

    /**
     * Get current user
     *
     * @return array
     */
    protected function getCurrentUser()
    {
        // get user data from Controller Plugin
        $userData = $this->currentUser()->getUserData();

        return $userData;
    }

    /**
     * Get current organisation
     *
     * @NOTE at the moment this will just return the users first organisation,
     * eventually the user will be able to select which organisation they are managing
     *
     * @return array
     */
    protected function getCurrentOrganisation()
    {
        $dto = MyAccount::create([]);
        $response = $this->handleQuery($dto);
        $data = $response->getResult();

        return $data['organisationUsers'][0]['organisation'];
    }

    /**
     * Get current organisation ID only
     *
     * @return int|null
     */
    protected function getCurrentOrganisationId()
    {
        $organisation = $this->getCurrentOrganisation();
        return (isset($organisation['id'])) ? $organisation['id'] : null;
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if (!$this->checkAccess($lvaId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        if ($this->lva === 'application' || $this->lva === 'variation') {

            $submissionRouteName = 'lva-' . $this->lva . '/submission-summary';
            $tmDetailsRouteName = 'lva-' . $this->lva . '/transport_manager_details';
            $matchedRouteName = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

            if ($matchedRouteName !== $submissionRouteName && $matchedRouteName !== $tmDetailsRouteName &&
                !$this->checkAppStatus($lvaId)
            ) {
                $this->redirect()->toRoute($submissionRouteName, ['application' => $lvaId]);
            }
        }

        return parent::checkForRedirect($lvaId);
    }

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Zend\Form\Form $form
     * @param array $variables
     * @return \Common\View\Model\Section
     */
    protected function render($titleSuffix, Form $form = null, $variables = array())
    {
        $this->attachCurrentMessages();

        if ($titleSuffix instanceof ViewModel) {
            return $titleSuffix;
        }

        $params = array_merge(
            array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form),
            $variables
        );

        return $this->renderView(new Section($params));
    }

    protected function renderView($section)
    {
        $template = $this->getRequest()->isXmlHttpRequest() ? 'ajax' : 'layout';

        $base = new ViewModel();
        $base->setTemplate('layout/' . $template)
            ->setTerminal(true)
            ->addChild($section, 'content');

        return $base;
    }
}
