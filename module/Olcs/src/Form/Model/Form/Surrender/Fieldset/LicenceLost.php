<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

class LicenceLost
{
    /**
     * @Form\Attributes({
     *     "value":"licence.surrender.licence.stolen.note"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = null;

    /**
     * @Form\Type("\Zend\Form\Element\Textarea")
     */
    public $details = null;
}
