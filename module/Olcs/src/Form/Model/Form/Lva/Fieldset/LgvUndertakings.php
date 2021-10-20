<?php

namespace Olcs\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lgv-undertakings")
 */
class LgvUndertakings
{
    /**
     * @Form\Attributes({"value": "markup-lgv-undertakings"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $lgvUndertakingsText = null;

    /**
     * @Form\Name("lgvDeclarationConfirmation")
     * @Form\Attributes({
     *   "id": "lgv-declaration-confirmation",
     *   "class" : "input--lgv-declaration-confirmation",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "lgv-undertakings.form.declaration.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1",
     *     "not_checked_message": "lgv-undertakings.form.declaration.error"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $lgvDeclarationConfirmation = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;
}
