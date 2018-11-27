<?php

namespace Olcs\Controller\Licence\Surrender;

class OperatorController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $params = [
            'title' => 'Where is your operator licence?',
            'licNo' => $this->licence['licNo'],
            'form' => $this->hlpForm->createForm(\Olcs\Form\Model\Form\Surrender\Operator::class),
            'backLink' => $this->getBackLink('lva-licence')
        ];

        $this->getServiceLocator()->get('Script')->loadFiles(['licence-surrender-operator']);
        return $this->renderView($params);
    }
}
