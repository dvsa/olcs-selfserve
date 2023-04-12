<?php

/**
 * Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\VariationBusinessDetails as CommonVariationBusinessDetails;

/**
 * Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetails extends CommonVariationBusinessDetails
{
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     */
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->getFormHelper()->remove($form, 'allow-email');

        $this->getFormServiceLocator()->get('lva-lock-business_details')->alterForm($form);
        $this->getFormHelper()->remove($form, 'form-actions->cancel');
    }
}
