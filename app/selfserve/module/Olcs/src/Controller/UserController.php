<?php

/**
 * User Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\CrudTableTrait;
use Olcs\View\Model\User;
use Olcs\View\Model\Form;
use Dvsa\Olcs\Transfer\Query\User\UserListSelfserve as ListDto;
use Dvsa\Olcs\Transfer\Query\User\UserSelfserve as ItemDto;
use Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve as CreateDto;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve as UpdateDto;
use Dvsa\Olcs\Transfer\Command\User\DeleteUserSelfserve as DeleteDto;

/**
 * User Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class UserController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait;
    use CrudTableTrait;

    /**
     * Dashboard index action
     */
    public function indexAction()
    {
        $crudAction = $this->checkForCrudAction();

        if (isset($crudAction)) {
            return $crudAction;
        }

        $params = [
            'page'    => $this->getPluginManager()->get('params')->fromQuery('page', 1),
            'sort'    => $this->getPluginManager()->get('params')->fromQuery('sort', 'id'),
            'order'   => $this->getPluginManager()->get('params')->fromQuery('order', 'DESC'),
            'limit'   => $this->getPluginManager()->get('params')->fromQuery('limit', 10),
        ];

        $params['query'] = $this->getPluginManager()->get('params')->fromQuery();

        $response = $this->handleQuery(
            ListDto::create(
                $params
            )
        );

        if ($response->isOk()) {
            $users = $response->getResult();
        } else {
            $this->getFlashMessenger()->addErrorMessage('unknown-error');
            $users = [];
        }

        $view = new User();
        $view->setServiceLocator($this->getServiceLocator());
        $view->setUsers($users, $params);

        $this->getServiceLocator()->get('Script')->loadFiles(['lva-crud']);

        return $view;
    }

    protected function save()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest('User', $this->getRequest());

        $id = $this->params()->fromRoute('id', null);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $this->formatSaveData($form->getData());

                if ((!empty($data['id']))) {
                    $command = UpdateDto::create($data);
                    $successMessage = 'manage-users.update.success';
                } else {
                    $command = CreateDto::create($data);
                    $successMessage = 'manage-users.create.success';
                }
                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->getFlashMessenger()->addSuccessMessage($successMessage);
                    return $this->redirectToIndex();
                } else {
                    $this->getFlashMessenger()->addErrorMessage('unknown-error');
                }
            }
        } elseif ($id) {
            $response = $this->handleQuery(
                ItemDto::create(
                    ['id' => $id]
                )
            );
            if (!$response->isOk()) {
                $this->getFlashMessenger()->addErrorMessage('unknown-error');
                return $this->redirectToIndex();
            }

            $data = $this->formatLoadData($response->getResult());
            $form->setData($data);
        }

        $view = new Form();
        $view->setForm($form);

        return $view;
    }

    public function deleteAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $response = $this->handleCommand(
                DeleteDto::create(
                    ['id' => $this->params()->fromRoute('id', null)]
                )
            );

            if ($response->isOk()) {
                $this->getFlashMessenger()->addSuccessMessage('manage-users.delete.success');
            } elseif ($response->isClientError()) {
                $this->getFlashMessenger()->addErrorMessage('manage-users.delete.error');
            } else {
                $this->getFlashMessenger()->addErrorMessage('unknown-error');
            }

            return $this->redirectToIndex();
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('GenericDeleteConfirmation', $request);

        $params = ['sectionText' => $this->getDeleteMessage()];

        return $this->render($this->getDeleteTitle(), $form, $params);
    }

    /**
     * Formats the data from what the service gives us, to what the form needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    public function formatLoadData($data)
    {
        return $this->getServiceLocator()
            ->get('BusinessRuleManager')
            ->get('UserMappingContactDetails')->{__FUNCTION__}($data);
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    public function formatSaveData($data)
    {
        return $this->getServiceLocator()
            ->get('BusinessRuleManager')
            ->get('UserMappingContactDetails')->{__FUNCTION__}($data);
    }

    /**
     * Gets a flash messenger object.
     *
     * @return \Common\Service\Helper\FlashMessengerHelperService
     */
    public function getFlashMessenger()
    {
        return $this->getServiceLocator()->get('Helper\FlashMessenger');
    }

    /**
     * Checks for crud actions.
     *
     * @return \Zend\Http\Response
     */
    public function checkForCrudAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            $crudAction = null;
            if (isset($data['table'])) {
                $crudAction = $this->getCrudAction(array($data));
            }

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction, ['add'], 'id', null);
            }
        }

        return null;
    }

    /**
     * Returns a params object. Made literal here.
     *
     * @return \Zend\Mvc\Controller\Plugin\Params
     */
    public function params()
    {
        return $this->getPluginManager()->get('params');
    }

    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return $this->getEvent()->getRequest();
    }

    /**
     * Add action - proxy method.
     *
     * @return mixed
     */
    public function addAction()
    {
        return $this->save();
    }

    /**
     * Add action - proxy method.
     *
     * @return mixed
     */
    public function editAction()
    {
        return $this->save();
    }

    /**
     * Redirects to index
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax('user', ['action' => 'index'], array(), false);
    }
}
