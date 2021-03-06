<?php
declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("switch-board-form")
 * @Form\Type("\Common\Form\Form")
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

    /**
     * @Form\Name("optionsFieldset")
     * @Form\ComposedObject("Olcs\Form\Model\Form\Vehicle\Fieldset\SwitchBoard")
     */
    public $optionsFieldset = null;

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
