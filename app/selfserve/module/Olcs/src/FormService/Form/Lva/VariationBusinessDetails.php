<?php

/**
 * Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\VariationBusinessDetails as CommonVariationBusinessDetails;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetails extends CommonVariationBusinessDetails implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->getFormServiceLocator()->get('lva-lock-business_details')->alterForm($form);
    }
}
