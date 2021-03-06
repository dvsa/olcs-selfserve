<?php
namespace Permits\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("CheckAnswers")
 * @Form\Attributes({"method":"POST"})
 * @Form\Type("Common\Form\Form")
 */

class IpaCheckAnswersForm
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large",
     *     "id":"submitbutton",
     *     "value":"confirm-and-return-to-overview",
     * })
     * @Form\Type("Laminas\Form\Element\Submit")
     */
    public $submit = null;
}
