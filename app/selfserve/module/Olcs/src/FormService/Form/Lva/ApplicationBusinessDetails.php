<?php

/**
 * Application Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\ApplicationBusinessDetails as CommonApplicationBusinessDetails;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
/**
 * Application Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetails extends CommonApplicationBusinessDetails implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        // if we have got any in force licences, lock the elements down
        if ($this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($params['orgId'])) {
            $this->getFormServiceLocator()->get('lva-lock-business_details')->alterForm($form);
        }
    }
}
