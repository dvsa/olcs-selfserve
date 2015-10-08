<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("UserRegistration")
 * @Form\Attributes({"method":"post","label":"user-registration.form.label"})
 * @Form\Options({"prefer_form_input_filter": true, "label": "user-registration.form.label"})
 */
class UserRegistration extends Base
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
     * @Form\Options({"label":"First name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"id":"familyName","placeholder":"","class":"medium", "required":false})
     * @Form\Options({"label":"Last name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"Email address"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\EmailAddress"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":60}})
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
     * @Form\Name("isLicenceHolder")
     * @Form\Options({
     *     "label": "user-registration.field.isLicenceHolder.label",
     *     "value_options":{
     *          "N":"select-option-no",
     *          "Y":"select-option-yes",
     *      },
     *      "fieldset-attributes" : {
     *          "class":"checkbox inline"
     *      }
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"isLicenceHolder", "placeholder":"", "value":"N"})
     * @Form\Type("Radio")
     */
    public $isLicenceHolder;

    /**
     * @Form\Type("Text")
     * @Form\Options({
     *     "label": "user-registration.field.licenceNumber.label",
     * })
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isLicenceHolder",
     *          "context_values": {"Y"},
     *          "validators": {
     *              {"name": "Zend\Validator\StringLength", "options": {"min": 2, "max": 35}}
     *          }
     *      }
     * })
     */
    public $licenceNumber = null;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium"})
     * @Form\Options({"label":"user-registration.field.organisationName.label"})
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isLicenceHolder",
     *          "context_values": {"N"},
     *          "validators": {
     *              {"name": "Zend\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $organisationName = null;

    /**
     * @Form\Type("DynamicRadio")
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "businessType",
     *          "class": "checkbox"
     *      },
     *     "label": "user-registration.field.businessType.label",
     *     "disable_inarray_validator": false,
     *     "category": "org_type",
     *     "exclude": {"org_t_ir"}
     * })
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isLicenceHolder",
     *          "context_values": {"N"},
     *          "validators": {
     *              {"name": "Zend\Validator\NotEmpty"}
     *          }
     *      }
     * })
     */
    public $businessType = null;
}
