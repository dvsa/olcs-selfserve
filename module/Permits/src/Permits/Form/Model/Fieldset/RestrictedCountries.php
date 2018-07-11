<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("RestrictedCountries")
 */
class RestrictedCountries
{
    /**
     * @Form\Name("restrictedCountries")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--restricted-countries",
     *   "id" : "restrictedCountriesRadio",
     * })
     * @Form\Options({
     *     "label": "",
     *     "short-label": "short-label-tol-licence-type",
     *     "label_attributes":{
     *          "class" : "form-control form-control--radio form-control--inline restrictedRadio"
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
     * @Form\Attributes({
     *      "allowWrap":true,
     *      "data-container-class": "form-control__container",
     *      "id" : "restrictedCountriesList",
     * })
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\RestrictedCountriesList")
     */
    public $restrictedCountriesList = null;

}
