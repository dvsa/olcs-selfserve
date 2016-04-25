<?php

namespace Olcs\Controller\Ebsr;

use Common\Exception\ResourceNotFoundException;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmissionList;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\TxcInboxList;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\BusRegWithTxcInbox as ItemDto;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\UpdateTxcInbox as UpdateTxcInboxDto;
use Dvsa\Olcs\Transfer\Query\Bus\RegistrationHistoryList as BusRegVariationHistoryDto;
use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\Rbac\User;

/**
 * Class BusRegistrationController
 */
class BusRegistrationController extends AbstractController
{
    /**
     * Lists all EBSR's with filter search form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $request = $this->getRequest();

            $postData = $request->getPost();

            if (isset($postData['action']) && isset($postData['table']) && $postData['table'] == 'txc-inbox') {
                return $this->processMarkAsRead($postData);
            }

            return $this->processSearch($postData);
        }

        $userData = $this->currentUser()->getUserData();

        $params = [
            'subType'   => $this->params()->fromQuery('subType'),
            'status'    => $this->params()->fromQuery('status'),
            'page'      => $this->params()->fromQuery('page', 1),
            'order'     => $this->params()->fromQuery('order', 'DESC'),
            'limit'     => $this->params()->fromQuery('limit', 25),
        ];

        if ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY) {
            $params['sort'] = $this->params()->fromQuery('sort', 'createdOn');
            $query = TxcInboxList::create($params);
        } else {
            $params['sort'] = $this->params()->fromQuery('sort', 'submittedDate');
            $query = EbsrSubmissionList::create($params);
        }

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

        $pageHeaderText = '';
        $pageHeaderUrl = '';
        if ($this->isGranted('selfserve-ebsr-upload')) {
            $pageHeaderText = 'bus-registrations-index-subtitle';
            $pageHeaderUrl = [
                'route' => 'bus-registration/ebsr',
                'params' => [
                    'action' => 'upload'
                ],
                'text' => 'register-cancel-update-service'
            ];
        }

        // setup layout and view
        $layout = $this->generateLayout(
            [
                'pageTitle' => 'bus-registrations-index-title',
                'pageHeaderText' => $pageHeaderText,
                'searchForm' => $filterForm,
                'pageHeaderUrl' => $pageHeaderUrl,
                'showNav' => false
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

        $userData = $this->currentUser()->getUserData();

        if ($userData['userType'] === User::USER_TYPE_LOCAL_AUTHORITY) {
            $tableName = 'txc-inbox';
        } else {
            $tableName = 'ebsr-submissions';
        }

        $busRegistrationTable = $tableBuilder->buildTable(
            $tableName,
            ['Results' => $result['results'], 'Count' => $result['count']],
            $params,
            false
        );

        return $busRegistrationTable;
    }
    /**
     * Process those marked in table as read
     *
     * @param $data
     * @return array
     */
    public function processMarkAsRead($data)
    {
        $command = UpdateTxcInboxDto::create(
            [
                'ids' => $data['id'],
            ]
        );

        $response = $this->handleCommand($command);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {

            $params['subType'] = $this->params()->fromQuery('subType');
            $params['status'] = $this->params()->fromQuery('status');

            return $this->redirect()->toRoute(null, $params, [], false);
        }
    }

    /**
     * Process the search, simply sets up the GET params and redirects
     *
     * @param $data
     */
    private function processSearch($data)
    {
        $params = $this->params()->fromQuery();

        $params['subType'] = empty($data['fields']['subType']) ? null : $data['fields']['subType'];
        $params['status'] = empty($data['fields']['status']) ? null : $data['fields']['status'];

        // initialise search results to page 1
        $params['page'] = 1;

        return $this->redirect()->toRoute(null, [], ['query' => $params], true);
    }

    /**
     * Bus registration details page
     *
     * @return array|ViewModel
     * @throws ResourceNotFoundException
     */
    public function detailsAction()
    {
        $id = $this->params()->fromRoute('busRegId');

        $query = ItemDto::create(['id' => $id]);

        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $results = $response->getResult();
        }
        $documents = [];

        if ($this->isGranted('selfserve-ebsr-documents')) {
            if (!empty($results['txcInboxs'][0]['pdfDocument'])) {
                $documents[] = $results['txcInboxs'][0]['pdfDocument'];
            }
            if (!empty($results['txcInboxs'][0]['routeDocument'])) {
                $documents[] = $results['txcInboxs'][0]['routeDocument'];
            }
            if (!empty($results['txcInboxs'][0]['zipDocument'])) {
                $documents[] = $results['txcInboxs'][0]['zipDocument'];
            }
        }

        // setup layout and view
        $content = $this->generateContent(
            'olcs/bus-registration/details',
            [
                'registrationDetails' => $results,
                'documents' => $documents,
                'variationHistoryTable' => $this->fetchVariationHistoryTable($results['id'])
            ]
        );

        return $content;
    }

    /**
     * Method to generate the Variation History table
     *
     * @param $busRegId
     * @return array|string
     */
    private function fetchVariationHistoryTable($busRegId)
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $query = BusRegVariationHistoryDto::create(
            [
                'id' => $busRegId,
                'sort' => 'variationNo',
                'order' => 'DESC'
            ]
        );

        $response = $this->handleQuery($query);

        // handle response
        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            return $tableBuilder->buildTable(
                'bus-reg-variation-history',
                $result,
                ['url' => $this->plugin('url')],
                false
            );
        }

        return null;
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
        $filterForm = $this->getServiceLocator()->get('Helper\Form')->createForm('BusRegApplicationsFilterForm');

        $filterForm->setData(
            [
                'fields' => [
                    'subType' => $params['subType'],
                    'status' => $params['status']
                ]
            ]
        );

        return $filterForm;
    }
}
