<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-lost")
 */
class LicenceLost
{
    /**
     * @Form\Attributes({
     *     "value":"licence.surrender.licence.lost.note"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = "LicenceLost";

    /**
     * @Form\Type("\Zend\Form\Element\Textarea")
     * @Form\Attributes({
     *     "id" : "any2"
     * })
     */
    public $details = null;
}
