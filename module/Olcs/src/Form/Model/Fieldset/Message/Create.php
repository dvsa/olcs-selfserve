<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Common\Form\Elements\InputFilters\ActionButton;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;

class Create
{
    /**
     * @Form\Options({
     *     "label": "messaging.subject",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\MessagingSubject"
     * })
     * @Form\Attributes({"id":"status","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $messageSubject;

    /**
     * @Form\Options({
     * *     "label": "messaging.app-or-lic-no",
     * *     "empty_option": "Please select",
     * *     "disable_inarray_validator": false,
     * *     "service_name": "Olcs\Service\Data\MessagingAppOrLicNo",
     *       "use_groups": true
     * * })
     * * @Form\Attributes({"id":"status","placeholder":""})
     * * @Form\Type("DynamicSelect")
     */
    public $appOrLicNo;

    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "maxlength": 1000
     * })
     * @Form\Options({"label": "You can enter up to 1000 characters"})
     * @Form\Required(true)
     * @Form\Type(\Laminas\Form\Element\Textarea::class)
     * @Form\Filter({"name": \Laminas\Filter\StringTrim::class})
     * @Form\Validator({
     *     "name": \Laminas\Validator\StringLength::class,
     *     "options": {"max":1000}
     * })
     */
    public ?TextArea $message = null;

    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--default",
     *     "id": "send"
     * })
     * @Form\Options({
     *     "label": "Send message"
     * })
     * @Form\Type(\Common\Form\Elements\InputFilters\ActionButton::class)
     */
    public ?ActionButton $create = null;
}
