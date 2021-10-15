<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-lgv-undertakings")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class LgvUndertakings
{
    /**
     * @Form\Name("lgvUndertakings")
     * @Form\ComposedObject("Olcs\Form\Model\Form\Lva\Fieldset\LgvUndertakings")
     */
    public $lgvUndertakings = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
