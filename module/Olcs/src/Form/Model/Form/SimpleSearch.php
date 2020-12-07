<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("simple-search")
 * @Form\Attributes({"method":"post", "action":"", "role":"search"})
 * @Form\Hydrator("Laminas\Stdlib\Hydrator\ArraySerializable")
 */
class SimpleSearch
{
    /**
     * @Form\Attributes({"class": "long", "placeholder": "", "label":"some"})
     * @Form\Type("Text")
     * @Form\Validator({"name": "NotEmpty"})
     * @Form\Options({
     *     "label": "search.form.label",
     *     "error-message": "simpleSearch_search-error"
     * })
     */
    protected $search;

    /**
     * @Form\Attributes({
     *    "class": "action--primary large",
     *    "value": "lookup-basic-search-submit"
     * })
     * @Form\Type("Submit")
     */
    protected $submit;

    /**
     * @Form\Type("Hidden")
     */
    protected $index;
}
