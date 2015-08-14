<?php

/**
 * Search Controller
 */
namespace Olcs\Controller\Search;

use Olcs\View\Model\Dashboard;
use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Olcs\Form\Model\Form\SimpleSearch;
use Common\Controller\Traits\ViewHelperManagerAware;
use Common\Service\Data\Search\SearchType;
use Common\Service\Data\Search\Search;
use Olcs\Form\Model\Form\SearchFilter as SearchFilterForm;

use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchDateRangeFieldset;

/**
 * Search Controller
 */
class SearchController extends AbstractController
{
    use ViewHelperManagerAware;

    public function jumpAction()
    {
        return $this->redirect()->toRoute('index', [], ['code' => 303], true);
    }

    /**
     * Search index action
     *
     * There should probably be a search box on this page I expect.
     */
    public function indexAction()
    {
        /** @var \Zend\Form\Form $form */
        $form = $this->getIndexForm(SimpleSearch::class);

        if ($this->getRequest()->isPost()) {

            $sd = $this->getIncomingSearchData();

            $form->setData($sd);

            if ($form->isValid()) {
                /**
                 * Remove the "index" key from the incoming parameters.
                 */
                $index = $sd['index'];
                unset($sd['index']);

                return $this->redirect()->toRoute(
                    'search',
                    ['index' => $index, 'action' => 'search'],
                    ['query' => $sd, 'code' => 303],
                    true
                );
            }
        }

        $form->get('index')->setValue($this->params()->fromRoute('index'));

        $view = new ViewModel(['searchForm' => $form]);
        $view->setTemplate('search/index-' . $this->params()->fromRoute('index') . '.phtml');
        return $view;
    }

    public function getIncomingSearchData()
    {
        $remove = [
            'controller',
            'action',
            'module',
            'submit'
        ];

        $incomingParameters = [];

        if ($routeParams = $this->params()->fromRoute()) {
            $incomingParameters += $routeParams;
        }

        if ($queryParams = (array) $this->params()->fromQuery()) {
            $incomingParameters = array_merge($incomingParameters, $queryParams);
        }

        if ($postParams = (array) $this->params()->fromPost()) {
            $incomingParameters = array_merge($incomingParameters, $postParams);
        }

        $search = '';

        if (isset($incomingParameters['search']) && $incomingParameters['search'] != '') {
            $search = $incomingParameters['search'];
        }

        // if "search" is an array within text on the filter form ... throws undefined index error otherwise
        if (isset($incomingParameters['text']) && isset($incomingParameters['text']['search'])) {

            $search = $incomingParameters['text']['search'];
        }

        $incomingParameters['search'] = $search;
        $incomingParameters['text']['search'] = $search;

        /**
         * Now remove all the data we don't want in the query string.
         */
        $incomingParameters = array_diff_key($incomingParameters, array_flip($remove));

        return $incomingParameters;
    }

    public function searchAction()
    {
        // this order 1
        $data = $this->getIncomingSearchData();
        $data['index'] = $this->params()->fromRoute('index');

        // this order 2
        $form = $this->initialiseFilterForm();
        /* @var \Zend\Form\Form $form */
        $form = $form->getValue();
        $form->get('index')->setValue($this->params()->fromRoute('index'));

        $view = new ViewModel(['index'=>$this->params()->fromRoute('index')]);

        $this->getSearchService()->setQuery($this->getRequest()->getQuery())
            ->setRequest($this->getRequest())
            ->setIndex($data['index'])
            ->setSearch($data['search']);

        $searchPostUrl = $this->url()->fromRoute('search', ['index' => $data['index'], 'action' => 'index'], [], true);
        $form->setAttribute('action', $searchPostUrl);

        $view->results = $this->getSearchService()->fetchResultsTable();

        $view->setTemplate('layouts/main-search-results.phtml');

        return $view;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getIndexForm($name)
    {
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm($name);
        $this->getServiceLocator()->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getFilterForm($name)
    {
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm($name);
        $this->getServiceLocator()->get('Helper\Form')->setFormActionFromRequest($form, $this->getRequest());
        return $form;
    }

    /**
     * Sets the navigation to that secified in the controller. Useful for when a controller is
     * 100% reresented by a single navigation object.
     *
     * @see $this->navigationId
     *
     * @return boolean true
     */
    public function setNavigationCurrentLocation()
    {
        $navigation = $this->getServiceLocator()->get('Navigation');
        if (!empty($this->navigationId)) {
            $navigation->findOneBy('id', $this->navigationId)->setActive();
        }

        return true;
    }

    /**
     * @return \Common\Form\Form
     */
    public function initialiseFilterForm()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getFilterForm(SearchFilterForm::class);
        $form->remove('csrf');

        // Index is required for filter fields as they are index specific.
        $index = $this->params()->fromRoute('index');

        if (isset($index)) {

            $this->getSearchService()->setIndex($index);

            // terms filters
            /** @var  $fs */
            $fs = $this->getServiceLocator()->get('FormElementManager')
                ->get(SearchFilterFieldset::class, ['index' => $index, 'name' => 'filter']);
            $form->add($fs);

            // date ranges
            $fs = $this->getServiceLocator()->get('FormElementManager')
                ->get(SearchDateRangeFieldset::class, ['index' => $index, 'name' => 'dateRanges']);
            $form->add($fs);
        }

        $form->populateValues($this->getIncomingSearchData());

        $form = $this->getServiceLocator()
            ->get('ViewHelperManager')
            ->get('placeholder')
            ->getContainer('searchFilter')
            ->set($form);

        return $form;
    }

    /**
     * @return Search
     */
    public function getSearchService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get(Search::class);
    }

    /**
     * @return SearchType
     */
    public function getSearchTypeService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get(SearchType::class);
    }
}
