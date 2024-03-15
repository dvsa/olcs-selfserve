<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Common\Form\Element\DynamicSelect;
use Common\Form\Elements\InputFilters\ActionButton;
use Common\Form\Elements\Types\GuidanceTranslated;
use Common\Form\Model\Fieldset\MultipleFileUpload;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;

class Create
{
    /**
     * @Form\Options({
     *     "label": "messaging.subject",
     *     "empty_option": "Please select",
     *     "service_name": \Common\Service\Data\MessagingSubject::class
     * })
     * @Form\Attributes({
     *     "class": "govuk-select"
     * })
     * @Form\Type(\Common\Form\Element\DynamicSelect::class)
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *         options={
     *             "messages": {
     *                 Laminas\Validator\NotEmpty::IS_EMPTY: "messaging.form.message.subject.empty.error_message"
     *             },
     *         },
     *         breakChainOnFailure=true,
     *         priority=100
     *    )
     */
    public ?DynamicSelect $messageSubject = null;

    /**
     * @Form\Options({
     *     "label": "messaging.app-or-lic-no",
     *     "empty_option": "Please select",
     *     "service_name": \Olcs\Service\Data\MessagingAppOrLicNo::class,
     *     "use_groups": true
     * })
     * @Form\Attributes({
     *     "class": "govuk-select"
     * })
     * @Form\Type(\Common\Form\Element\DynamicSelect::class)
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *          options={
     *              "messages": {
     *                  Laminas\Validator\NotEmpty::IS_EMPTY: "messaging.form.message.app_or_lic_no.empty.error_message"
     *              },
     *          },
     *          breakChainOnFailure=true,
     *          priority=100
     *     )
     */
    public ?DynamicSelect $appOrLicNo = null;

    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "maxlength": 1000
     * })
     * @Form\Options({
     *     "label": "",
     *     "hint": "You can enter up to 1000 characters",
     * })
     * @Form\Type(\Laminas\Form\Element\Textarea::class)
     * @Form\Filter(\Laminas\Filter\StringTrim::class)
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *       options={
     *           "messages": {
     *               Laminas\Validator\NotEmpty::IS_EMPTY: "messaging.form.message.content.empty.error_message"
     *           },
     *       },
     *       breakChainOnFailure=true
     *  )
     * @Form\Validator(\Laminas\Validator\StringLength::class,
     *     options={
     *         "min": 5,
     *         "max": 1000,
     *         "messages": {
     *              Laminas\Validator\StringLength::TOO_SHORT: "messaging.form.message.content.too_short.error_message",
     *              Laminas\Validator\StringLength::TOO_LONG: "messaging.form.message.content.too_long.error_message",
     *          }
     *     }
     * )
     */
    public ?TextArea $messageContent = null;

    /**
     * @Form\Name("file")
     * @Form\Attributes({"id": "file"})
     * @Form\ComposedObject(MultipleFileUpload::class)
     */
    public ?MultipleFileUpload $file = null;

    /**
     * @Form\Attributes({"value": "markup-messaging-new-conversation-timeframe"})
     * @Form\Type(\Common\Form\Elements\Types\GuidanceTranslated::class)
     */
    public ?GuidanceTranslated $guidance = null;

    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--default",
     *     "id": "send"
     * })
     * @Form\Options({
     *     "label": "Send message",
     * })
     * @Form\Type(\Common\Form\Elements\InputFilters\ActionButton::class)
     */
    public ?ActionButton $create = null;
}
