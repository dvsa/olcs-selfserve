<?php

namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Licence")
 */
class Licence
{
    /**
     * @Form\Name("licence")
     * @Form\Required(true)
     * @Form\Attributes({"class": "govuk-radios__input"})
     * @Form\Options({
     *      "label_attributes":{"class": "govuk-label govuk-radios__label govuk-label--s"}
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\NotEmpty",
     *      "options": {
     *          "messages":{Zend\Validator\NotEmpty::IS_EMPTY:"error.messages.licence"}
     *      }
     * })
     * @Form\Type("DynamicRadio")
     */
    public $licence = null;
}
