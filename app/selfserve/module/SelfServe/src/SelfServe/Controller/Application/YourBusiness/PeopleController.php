<?php

/**
 * People Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * People Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends YourBusinessController
{
    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        // @todo For now skip this section
        //return $this->renderSection();
        return $this->goToNextStep();
    }

    /**
     * Save data
     *
     * @param array $data
     */
    public function save($data)
    {
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    public function load($id)
    {
        return array('data' => array());
    }
}
