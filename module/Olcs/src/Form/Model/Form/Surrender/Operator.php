<?php

namespace Olcs\Form\Model\Form\Surrender;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("operator")
 */
class Operator
{
    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\Fieldset\LicenceDocument")
     */
    public $licenceDocument = null;
}
