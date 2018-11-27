<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-document")
 */
class LicenceDocument
{
    /**
     * @Form\Options({
     *     "label": "",
     *     "label_attributes": {
     *         "class":"form-control form-control--radio form-control--advanced"
     *     },
     *     "value_options": {
     *          "posession": {
     *              "label": "In your possession",
     *              "value": "possession",
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceInPossession",
     *                  "attributes": {"id":"surrender-licence-possession","class":"govuk-inset-text"}
     *              }
     *          },
     *          "lost": {
     *              "label": "Lost",
     *              "value": "lost",
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceLost",
     *                  "attributes": {"id":"surrender-licence-lost","class":"govuk-inset-text"}
     *              }
     *          },
     *          "stolen": {
     *              "label": "Stolen",
     *              "value": "stolen",
     *              "childContent" : {
     *                  "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceStolen",
     *                  "attributes": {"id":"surrender-licence-stolen","class":"govuk-inset-text"}
     *              }
     *          }
     *      },
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     },
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $licenceDocumentOptions = null;
}
