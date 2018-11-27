<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

class LicenceInPossession
{
    /**
     * @Form\Options({
     *     "label":"You must destroy your operator licence"
     * })
     * @Form\Type("\Common\Form\Elements\Types\PlainText")
     */
    public $notice = null;
}
