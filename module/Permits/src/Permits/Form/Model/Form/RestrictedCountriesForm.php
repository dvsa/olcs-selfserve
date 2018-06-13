<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("restrictedCountries")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Permits\Form\Form")
 */
class RestrictedCountriesForm
{

    /**
     * @Form\Name("restrictedCountries")
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Attributes({
     *   "class" : "input--trips",
     * })
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "form-control form-control--radio restrictedRadio"
     *     },
     *     "value_options":{
     *          "1" : "Yes",
     *          "0" : "No"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $restrictedCountries = null;

    /**
     * @Form\Name("restrictedCountriesList")
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "form-control form-control--checkbox"
     *     }
     * })
     * @Form\Type("MultiCheckBox")
     */
    public $restrictedCountriesList = null;

    /**
     * @Form\Name("submit")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submitbutton",
     *     "value":"Save and continue",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submitButton = null;

    /**
     * @Form\Name("test")
     * @Form\Type("Zend\Form\Element\Text")
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":35}})
     */
    public $test = null;

}
