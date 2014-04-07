<?php

/**
 * licence type Controller
 *
 *
 * @package		selfserve
 * @subpackage          operating-centre
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\LicenceType;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

class IndexController extends FormJourneyActionController{
    
    const LICENCE_PARAM_NAME = 'id';

    protected $messages;

    public function __construct()
    {
        $this->setCurrentSection('licence-type');
    }
    
    public function generateStepFormAction() {
        $licenceId = $this->params()->fromRoute('licenceId');
        $step = $this->params()->fromRoute('step');

        $this->setCurrentStep($step);
        
        // create form
        $form = $this->generateSectionForm();
        
        // Do the post
        $form = $this->formPost($form, $this->getStepProcessMethod($this->getCurrentStep()), ['licenceId' => $licenceId]);

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }
        
        // render the view
        $view = new ViewModel(['licenceTypeForm' => $form]);
        $view->setTemplate('self-serve/licence/index');
        return $view;
    }
    
		
    /**
     * Method to process the operator location. 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processOperatorLocation($valid_data, $form, $params)
    {
        $data['version'] = 1;
        $data['licenceNumber'] = '';
        $data['licenceType'] = '';
        $data['status'] = 'lic_status.new';

        // create licence
        //$licence = $this->processAdd($data, 'Licence');
        //var_dump($licence);exit;

        // create application
        //$application = $this->processAdd($data, 'Application');
        
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/licence-type', 
                                    array('licenceId' => $params['licenceId'], 'step' => $next_step));
        
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getOperatorLocationFormData()
    {
    	return array();
    }
    
    
    
    /**
     * Method to process the operator type. 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processOperatorType($valid_data, $form, $params)
    {
        // data persist goes here
        
        $next_step = $this->evaluateNextStep($form);
        $this->redirect()->toRoute('selfserve/licence-type', 
                                array('licenceId' => $params['licenceId'], 
                                      'step' => $next_step));
    }
    
    
    /**
     * Returns persisted data (if exists) to popuplate form
     * 
     * @return array
     */
    public function getOperatorTypeFormData()
    {
    	$entity = $this->_getLicenceEntity();
    	if (empty($entity))
    	    return array();
    	
        return array(
            'operator-type' => array(
    	        'operator-type' => $entity['goodsOrPsv'], 
            ),
    	);
    }
    
    
    /**
     * Method to process the licence type. 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLicenceType($valid_data, $form, $params)
    {
        // data persist goes here

        $this->redirect()->toRoute('selfserve/licence-type-complete', 
                                array('licenceId' => $params['licenceId'], 
                                      'step' => $next_step));
    }
    
    /**
     * Returns persisted data (if exists) to popuplate form
     *
     * @return array
     */
    public function getLicenceTypeFormData()
    {
    	$entity = $this->_getLicenceEntity();
    	if (empty($entity))
    	    return array();
    	
    	return array(
    	    'licence-type' => array(
    		    'licence_type' => $entity['licenceType'],
    	    ),
    	);
    }
    
    /**
     * Method to process the licence type for PSV type operators 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLicenceTypePsv($valid_data, $form, $params)
    {
        // data persist goes here

        $this->redirect()->toRoute('selfserve/licence-type-complete', 
                                array('licenceId' => $params['licenceId'], 
                                      'step' => $next_step));

 
    }
    
    /**
     * Method to process the licence type for NI.
     * Should insist that goods_or_psv = goods? 
     * 
     * @param array $valid_data
     * @param \Zend\Form $form
     * @param array $journeyData
     * @param array $params
     */
    public function processLicenceTypeNi($valid_data, $form, $params)
    {
        // data persist goes here

        $this->redirect()->toRoute('selfserve/licence-type-complete',  
                                array('licenceId' => $params['licenceId'], 
                                      'step' => $next_step));
 
    }
    
    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {
        $licenceId = $this->params()->fromRoute('licenceId');

        // persist data if possible
        $request  = $this->getRequest();
        $this->redirect()->toRoute('selfserve/business-type', 
                                array('licenceId' => $licenceId, 'step' => 
                                 'business-type'));
    }
    
    /**
     * Get licence entity based on route id value
     * 
     * @return array|false
     */
    private function _getLicenceEntity()
    {
    	$licenceId = (int) $this->params()->fromRoute(self::LICENCE_PARAM_NAME);
    	if ($licenceId == 0)
    		return array();
    	 
    	$result = $this->makeRestCall('Licence', 'GET', array('id' => $licenceId));
    	if (empty($result)) {
    		//not found action?
    		return false;
    	}
    	return $result;
    }


}
