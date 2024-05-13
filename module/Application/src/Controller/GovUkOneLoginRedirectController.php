<?php

namespace Dvsa\Olcs\Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class GovUkOneLoginRedirectController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toUrl('/govukaccount-redirect.php');
    }
}
