<?php

namespace Olcs\Controller\BusReg;

use Common\Exception\ResourceNotFoundException;
use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList as ListDto;
use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\Rbac\User;

/**
 * Class BusRegRegistrationsController
 */
class BusRegRegistrationsController extends AbstractController
{
    /**
     * Lists all Bus Reg's with filter search form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $request = $this->getRequest();
            $postData = $request->getPost();

            return $this->processSearch($postData);
        }

        $userData = $this->currentUser()->getUserData();

        $params = [
            'organisationName'  => $this->params()->fromQuery('organisationName'),
            'status'            => $this->params()->fromQuery('status'),
            'licNo'             => $this->params()->fromQuery('licNo'),
            'page'              => $this->params()->fromQuery('page', 1),
            'order'             => $this->params()->fromQuery('order', 'ASC'),
            'limit'             => $this->params()->fromQuery('limit', 25),
        ];

        $params['sort'] = $this->params()->fromQuery('sort', 'licNo, routeNo');

        if ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY) {
            $params['organisationName'] = null;
        } else {
            $params['organisationName'] = $userData['organisation']['name'];
        }

        $query = ListDto::create($params);
        // set query params for pagination
        $params['query'] = $params;

        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentUnknownError();
        }

        $busRegistrationTable = '';
        if ($response->isOk()) {
            $result = $response->getResult();
            $busRegistrationTable = $this->generateTable($result, $params);
        }

        $filterForm = $this->getFilterForm($params);

        // setup layout and view
        $layout = $this->generateLayout(
            [
                'pageTitle' => 'bus-registrations-index-title',
                'searchForm' => $filterForm,
                'activeTab' => 'registrations',
                'showNav' => false,
                'tabs' => $this->generateTabs()
            ]
        );

        $content = $this->generateContent(
            'olcs/bus-registration/index',
            [
                'busRegistrationTable' => $busRegistrationTable,
            ]
        );

        $layout->addChild($content, 'content');

        return $layout;
    }

    /**
     * Generates one of two tables depending on user logged in.
     * LAs get the txc-inbox table to match the results returned. Operators get the ebsr-submissions table.
     *
     * @param $result
     * @param $params
     * @return string
     */
    private function generateTable($result, $params)
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $tableName = 'busreg-registrations';

        $busRegistrationTable = $tableBuilder->buildTable(
            $tableName,
            ['Results' => $result['results'], 'Count' => $result['count']],
            $params,
            false
        );

        return $busRegistrationTable;
    }

    /**
     * Process the search, simply sets up the GET params and redirects
     * @param $data
     * @return \Zend\Http\Response
     */
    private function processSearch($data)
    {
        $params = $this->params()->fromQuery();

        $params['organisationName'] = empty($data['fields']['organisationName']) ?
            null : $data['fields']['organisationName'];
        $params['status'] = empty($data['fields']['status']) ? null : $data['fields']['status'];
        $params['licNo'] = empty($data['fields']['licNo']) ? null : $data['fields']['licNo'];

        // initialise search results to page 1
        $params['page'] = 1;

        return $this->redirect()->toRoute(null, [], ['query' => $params], true);
    }

    /**
     * Set up the layout with title, subtitle and content
     *
     * @param null $title
     * @param null $subtitle
     * @return \Zend\View\Model\ViewModel
     */
    private function generateLayout($data = [])
    {
        $layout = new \Zend\View\Model\ViewModel(
            $data
        );

        $layout->setTemplate('layouts/search');

        return $layout;
    }

    /**
     * Generate page content
     *
     * @param $template
     * @param array $data
     * @return ViewModel
     */
    private function generateContent($template, $data = [])
    {
        $content = new ViewModel($data);

        $content->setTemplate($template);
        return $content;
    }

    /**
     * Get and setup the filter form
     *
     * @param $params
     * @return mixed
     */
    public function getFilterForm($params)
    {
        $filterForm = $this->getServiceLocator()->get('Helper\Form')->createForm('BusRegRegistrationsFilterForm');

        if ($this->currentUser()->getUserData()['userType'] !== User::USER_TYPE_LOCAL_AUTHORITY) {
            // remove Organisation name filter for organisations
            $filterForm->get('fields')->remove('organisationName');
        } else {
            // removed licence no filter for LAs
            $filterForm->get('fields')->remove('licNo');
        }

        $filterForm->setData(
            [
                'fields' => [
                    'organisationName' => $params['organisationName'],
                    'licNo' => $params['licNo'],
                    'status' => $params['status']
                ]
            ]
        );

        return $filterForm;
    }

    /**
     * Privagte method to generate the tabs config array. Only operators and LAs can see the tabs. This *should* never
     * be executed by any other user type because of RBAC.
     *
     * @return array
     */
    private function generateTabs()
    {
        if (in_array(
            $this->currentUser()->getUserData()['userType'],
            [
                User::USER_TYPE_LOCAL_AUTHORITY,
                User::USER_TYPE_OPERATOR
            ]
        )) {
            return [
                0 => [
                    'label' => 'busreg-tab-title-registrations',
                    'route' => 'busreg-registrations',
                    'active' => true
                ],
                1 => [
                    'label' => 'busreg-tab-title-applications',
                    'route' => 'bus-registration'
                ]
            ];
        }
        return [];
    }
}
