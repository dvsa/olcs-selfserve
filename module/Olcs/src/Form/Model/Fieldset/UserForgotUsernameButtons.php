<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class UserForgotUsernameButtons
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "user-forgot-username.form-actions.submit.label"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;
}
