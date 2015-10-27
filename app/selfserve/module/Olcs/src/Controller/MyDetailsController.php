<?php

/**
 * My Details Controller
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Olcs\View\Model\Form;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as ItemDto;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccountSelfserve as UpdateDto;

/**
 * My Details Controller
 */
class MyDetailsController extends AbstractController
{
    public function editAction()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest('MyDetails', $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $this->formatSaveData($form->getData());

                $response = $this->handleCommand(
                    UpdateDto::create($data)
                );

                if ($response->isOk()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addSuccessMessage('generic.updated.success');
                    return $this->redirectToIndex();
                } else {
                    $result = $response->getResult();

                    if (!empty($result['messages']['loginId'])) {
                        $form->setMessages(
                            [
                                'main' => $result['messages']
                            ]
                        );
                    } else {
                        $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                    }
                }
            }
        } else {
            $response = $this->handleQuery(ItemDto::create([]));

            if ($response->isOk()) {
                $data = $this->formatLoadData($response->getResult());
                $form->setData($data);
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        $view = new Form();
        $view->setForm($form);

        return $view;
    }

    /**
     * Formats the data from what the service gives us, to what the form needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    private function formatLoadData($data)
    {
        $output = [];
        $output['main']['id']            = $data['id'];
        $output['main']['version']       = $data['version'];
        $output['main']['loginId']       = $data['loginId'];
        $output['main']['emailAddress']  = $data['contactDetails']['emailAddress'];
        $output['main']['emailConfirm']  = $data['contactDetails']['emailAddress'];
        $output['main']['familyName']    = $data['contactDetails']['person']['familyName'];
        $output['main']['forename']      = $data['contactDetails']['person']['forename'];

        return $output;
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    private function formatSaveData($data)
    {
        $output = [];
        $output['id'] = $data['main']['id'];
        $output['version'] = $data['main']['version'];
        $output['loginId'] = $data['main']['loginId'];
        $output['contactDetails']['emailAddress'] = $data['main']['emailAddress'];
        $output['contactDetails']['person']['familyName'] = $data['main']['familyName'];
        $output['contactDetails']['person']['forename']   = $data['main']['forename'];

        return $output;
    }

    /**
     * Redirects to index
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRoute('my-details', ['action' => 'edit'], array(), false);
    }
}
