<?php

namespace Olcs\FormService\Form\Lva;

/**
 * Licence Psv Vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicencePsvVehicles extends PsvVehicles
{
    /**
     * Alter form
     *
     * @param \Zend\Form\Form $form form
     *
     * @return \Zend\Form\Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->removeFormAction($form, 'saveAndContinue');
        $form->get('form-actions')->get('save')->setAttribute('class', 'action--primary large');
        return $form;
    }
}
