<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("UserDetails")
 * @Form\Attributes({"method":"post","label":"User details"})
 * @Form\Options({
 *     "prefer_form_input_filter": true, 
 *     "label": "User details",
 *     "label_attributes": {
 *         "class": "modal__title" 
 *     }
 * })
 */
class UserDetails extends Base
{
    /**
     * @Form\Options({"label":"Username"})
     * @Form\Required(true)
     * @Form\Attributes({"id":"username","placeholder":"","class":"medium", "required":false})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":40}})
     */
    public $loginId = null;

    /**
     * @Form\Attributes({"id":"forename","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"First name"})
     * @Form\Type("Text")
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({"label":"Last name"})
     * @Form\Type("Text")
     * @Form\AllowEmpty(true)
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Email address"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\EmailConfirm","options":{"token":"emailConfirm"}})
     */
    public $emailAddress = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Confirm email address"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $emailConfirm = null;

    /**
     * @Form\Name("permission")
     * @Form\Options({
     *     "label": "manage-users.field.permission.label",
     *     "value_options":{
     *          "tm":"manage-users.field.permission.tm.label",
     *          "user":"manage-users.field.permission.user.label",
     *          "admin":"manage-users.field.permission.admin.label",
     *      },
     *      "fieldset-attributes" : {
     *          "class":"checkbox"
     *      }
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"permission", "placeholder":"", "value":"user"})
     * @Form\Type("Radio")
     */
    public $permission;

    /**
     * @Form\Attributes({"id":"translateToWelsh","placeholder":""})
     * @Form\Options({
     *     "label": "translate-to-welsh",
     *     "checked_value":"Y",
     *     "unchecked_value":"N"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $translateToWelsh = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $currentPermission = null;
}
