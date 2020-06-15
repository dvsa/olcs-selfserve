<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Qa")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\QaForm")
 */
class QaBilateralForm
{
    /**
     * @Form\Name("qa")
     * @Form\Type("Zend\Form\Fieldset")
     */
    public $fieldset = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SubmitOnly")
     * @Form\Flags({"priority": -1})
     */
    public $submitButton = null;
}
