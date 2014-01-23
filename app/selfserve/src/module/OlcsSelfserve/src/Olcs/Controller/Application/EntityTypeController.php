<?php

/**
 * Details of application.
 *
 * OLCS-437
 *
 * @package		olcs
 * @subpackage	application
 * @author		J Rowbottom <joel.rowbottom@valtech.co.uk>
 */

namespace Olcs\Controller\Application;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;
use Zend\Session\Container;
use DateTime;

class EntityTypeController extends AbstractActionController
{

    public $messages = null;

    protected $applicationId;
    protected $application;
    
    public function indexAction() 
    {

        
    }
        
    public function detailsAction() {
 
        // get application
        $this->applicationId = $this->getEvent()->getRouteMatch()->getParam('appId');
        
        $appService = $this->service('Olcs\Application');
        $this->application = $appService->get($this->applicationId);

        $this->licenceId = $this->application['applicationLicence']['licenceId'];
        $this->licence = $this->service('Olcs\Licence')->get($this->licenceId);
        
        $this->organisation = $this->service('Olcs\Organisation')->get($this->licence['operator']['operatorId']);

        // to do need to extract entity type from application
        $entityType = 'Registered Company';
        if (isset($this->application)) {
           
            switch($entityType)
            {
                case 'Registered Company':
                    $params['action'] =  'registeredCompany';
                    break;
                case 'Sole Trader':
                    $params['action'] =  'soleTrader';
                    break;
                case 'Partnership':
                    $params['action'] =  'partnership';
                    break;
                case 'Public Authority':
                    $params['action'] =  'publicAuthority';
                    break;
                case 'Other':
                    $params['action'] =  'other';
                    break;
                default:
                      $this->getResponse()->setStatusCode(404);
                      return;
            }

            return $this->forward()->dispatch('Olcs\Controller\Application\EntityType', $params);
        } else {
            // if no valid application details, redirect back to page 1? 404 for now
            return $this->redirect()->toURL('/application/new');
        }
        
        
    }

    /**
     * Action to show registered company details
     */
    public function registeredCompanyAction()
    {

        $data = $this->getMappedEntityTypeData();

        /*if (isset($this->application['applicationReceivedOn']['date'])) {
            $data['dateApplicationReceived'] = $data['dateApplicationReceived']['year'] . '-' .
                $data['dateApplicationReceived']['month'] . '-' .
                $data['dateApplicationReceived']['day'];
        }*/

        $companyForm = new Form\Application\RegisteredCompanyDetailsForm();
        $officeForm = new Form\Application\RegisteredOfficeDetailsForm();
        $ownerForm = new Form\Application\IdListForm('owners');
        $subsidiaryForm = new Form\Application\IdListForm('subsidiaries');

        $detailsForm =  new Form\Application\DetailsForm();
        $detailsForm->add($companyForm->setData($data));
        $detailsForm->add($officeForm->setData($data));
        $detailsForm->add($ownerForm->setData($data));
        $detailsForm->add($subsidiaryForm->setData($data));

        if ($this->getRequest()->isPost() && array_key_exists('operatorId', $data)) {
            $applicationId = $this->createApplicationDetails($data);
        }

        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'create new application');

        $view = new ViewModel(array('detailsForm' => $detailsForm,
                                    'messages' => $this->messages,
                                    'application_header_details' => $this->getApplicationHeaderDetails($this->licence),
                                    'entityType' => $this->licence['operator']['entityType'],
                                    'licenceType' => $this->application['applicationLicence']['licenceType'],
                                    'operatorType' => $this->application['applicationLicence']['goodsOrPsv'],
                                    'applicationId' => isset($this->applicationId) ? $this->applicationId : null
                                ));

        $view->setTemplate('olcs/application/details');
        return $view;        
        
    }
    
    private function getMappedEntityTypeData() {
        
        return array(
            'companyNumId' => $this->licence['operator']['registeredCompanyNumber'],
            'operatorId' => $this->licence['operator']['operatorId'],
            'operatorNameTextHidden' => $this->licence['operator']['operatorName'],
            'tradingDropdown' => $this->licence['tradeType'],
            'tradingNames' => $this->licence['tradingNames'],
            'postcode' => isset($this->organisation['registeredAddress']['postcode']) ? $this->organisation['registeredAddress']['postcode'] : '',
            'addressLine1' => isset($this->organisation['registeredAddress']['line1']) ? $this->organisation['registeredAddress']['line1'] : '',
            'addressLine2' => isset($this->organisation['registeredAddress']['line2']) ? $this->organisation['registeredAddress']['line2'] : '',
            'addressLine3' => isset($this->organisation['registeredAddress']['line3']) ? $this->organisation['registeredAddress']['line3'] : '',
            'addressLine4' => isset($this->organisation['registeredAddress']['line4']) ? $this->organisation['registeredAddress']['line4'] : '',
            'townCity' => isset($this->organisation['registeredAddress']['town']) ? $this->organisation['registeredAddress']['town'] : '',
            'country' => isset($this->organisation['registeredAddress']['country']) ? $this->organisation['registeredAddress']['country'] : ''
                    );
    }
    
    /**
     * Action to process Sole trader details
     */
    public function soleTraderAction()
    {
        $data = $this->params()->fromPost();

        if (is_array($data['dateApplicationReceived'])) {
            $data['dateApplicationReceived'] = $data['dateApplicationReceived']['year'] . '-' .
                $data['dateApplicationReceived']['month'] . '-' .
                $data['dateApplicationReceived']['day'];
        }

        $personSearchForm = new Form\Application\PersonSearchForm();
        $personSearchForm->setAttribute('action', '/application/search/person?' . http_build_query(array(
            'type' => 'application-sole-trader',// used to set the header on the popup form
            'fieldgroup' => '#personSearchForm',
        )));

        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'create new application');
        
        $applicationNewForm = new Form\Application\NewForm();

        $view = new ViewModel(array('applicationNewForm' => $applicationNewForm,
                                    'messages' => $this->messages,
                                    'application_header_details' => $data,
                                    'personSearchForm' => $personSearchForm
                                    ));
        $view->setTemplate('olcs/application/soletrader-details-wrapper');

        return $view;        
        
    }
    
    /**
     * Action to process Sole trader details
     */
    public function partnershipAction()
    {
        echo 'partnership page';exit;
        
    }

    /**
     * Method to extract the application details from the licence data
     * 
     * @return array
     */
    private function getApplicationHeaderDetails()
    {   
        //@todo revise this and check dates are correct
        return array('operatorTypes' => $this->licence['goodsOrPsv'],
                     'licenceTypes' => $this->licence['licenceType'],
                     'entityTypes' => $this->licence['operator']['entityType'],
                     'dateApplicationReceived' =>    date('d-m-Y', strtotime($this->application['applicationReceivedOn']['date'])),
                     'trafficAreaType' => $this->application['applicationTrafficArea']['areaname'],
                     'licenceNumber' => $this->licence['licenceNumber'],
                     'applicationNumber' => $this->application['applicationNumber']
                );
    }
}
