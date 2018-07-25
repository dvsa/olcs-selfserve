<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("CancelApplication")
 */
class CancelApplication
{
    /**
     * @Form\Name("ConfirmCancel")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--confirm-cancel",
     *   "id" : "ConfirmCancel",
     * })
     * @Form\Options({
     *   "checked_value": "Yes",
     *     "unchecked_value": "No",
     *     "label": "permits.form.cancel_application.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "Yes",
     *     "value": "I confirm that I would like to cancel my application."
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */

    public $confirmCancel = null;
}
