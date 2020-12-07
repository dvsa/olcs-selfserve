<?php
namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("ContinueButton")
 */
class ContinueButton
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submit-continue-button",
     *     "value":"permits.button.continue"
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $submit = null;
}
