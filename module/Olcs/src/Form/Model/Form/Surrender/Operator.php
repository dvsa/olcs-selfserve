<?php

namespace Olcs\Form\Model\Form\Surrender;

use Zend\Form\Annotation as Form;

class Operator
{
    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceInPossession")
     */
    public $possesion = null;

    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceLost")
     */
    public $lost = null;

    /**
     * @Form\ComposedObject("\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceStolen")
     */
    public $stolen = null;

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
     *              "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceInPossession"
     *          },
     *          "lost": {
     *              "label": "Lost",
     *              "value": "lost",
     *              "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceLost"
     *          },
     *          "stolen": {
     *              "label": "Stolen",
     *              "value": "stolen",
     *              "content": "\Olcs\Form\Model\Form\Surrender\Fieldset\LicenceStolen"
     *          }
     *      },
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     },
     * })
     * @Form\Type("\Common\Form\Elements\Custom\MultiCheckBoxContent")
     */
    public $isDigitallySigned = null;
}
