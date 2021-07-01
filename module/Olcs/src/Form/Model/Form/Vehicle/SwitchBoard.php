<?php
declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("switch-board-form")
 * @Form\Type("\Common\Form\Form")
 * @Form\Options({"use_input_filter_defaults": false})
 */
class SwitchBoard
{
    const FIELD_OPTIONS_FIELDSET_NAME = 'optionsFieldset';
    const FIELD_OPTIONS_NAME = 'options';
    const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD = 'add';
    const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE = 'remove';
    const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER = 'transfer';
    const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT = 'reprint';
    const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW = 'view';
    const FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED = 'view-removed';

    /**
     * @Form\Options({
     *     "label_attributes": {
     *         "class": "form-control form-control--radio form-control--advanced"
     *     },
     *     "hint": "Select an option to manage your vehicles",
     *     "value_options": {
     *          "add": {
     *              "label": "licence.vehicle.switchboard.form.add.label",
     *              "value": "add",
     *              "attributes": {
     *                  "id":"add-vehicle"
     *              },
     *          },
     *          "remove": {
     *              "label": "licence.vehicle.switchboard.form.remove.label",
     *              "value": "remove",
     *              "attributes": {
     *                  "id":"remove-vehicle"
     *              },
     *          },
     *          "reprint": {
     *              "label": "licence.vehicle.switchboard.form.reprint.label",
     *              "value": "reprint",
     *              "attributes": {
     *                  "id":"reprint-vehicle"
     *              },
     *          },
     *          "transfer": {
     *              "label": "licence.vehicle.switchboard.form.transfer.label",
     *              "value": "transfer",
     *              "attributes": {
     *                  "id":"transfer-vehicle"
     *              },
     *          },
     *          "view": {
     *              "label": "licence.vehicle.switchboard.form.view.label",
     *              "value": "view",
     *              "attributes": {
     *                  "id":"view-vehicles"
     *              },
     *          },
     *          "view-removed": {
     *              "label": "licence.vehicle.switchboard.form.view.label-removed",
     *              "value": "view-removed",
     *              "attributes": {
     *                  "id":"view-removed-vehicles"
     *              },
     *          },
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Input("\Common\InputFilter\ChainValidatedInput")
     * @Input("\Common\InputFilter\ChainValidatedInput")
     * @Form\Validator({
     *     "name":"Laminas\Validator\InArray",
     *     "options": {
     *         "haystack": {
     *             "add", "remove", "reprint", "transfer", "view", "view-removed"
     *         },
     *         "strict": true,
     *         "messages": {
     *             Laminas\Validator\InArray::NOT_IN_ARRAY: "licence.vehicle.switchboard.form.error.select-option"
     *         }
     *     }
     * })
     */
    public $options = null;

    /**
     * @Form\Attributes({
     *     "id": "next",
     *     "value": "Next",
     *     "class": "action--primary large",
     *     "title": "licence.vehicle.switchboard.form.next.title"
     * })
     * @Form\Type("Submit")
     */
    public $formActions = null;
}
