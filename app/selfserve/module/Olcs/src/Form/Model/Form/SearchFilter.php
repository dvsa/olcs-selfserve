<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("search-filter")
 * @Form\Attributes({"method":"post", "class": "filters  form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class SearchFilter
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $index = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Text")
     */
    public $search = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "search.form.filter.update_button"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;
}
