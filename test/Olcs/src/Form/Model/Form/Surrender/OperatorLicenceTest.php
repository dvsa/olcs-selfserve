<?php

namespace OlcsTest\Form\Model\Form\Surrender;

use Common\Form\Elements\InputFilters\ActionButton;
use Common\Form\Elements\Types\Radio;
use Common\Form\Elements\Types\HtmlTranslated;
use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Textarea;

class OperatorLicenceTest extends AbstractFormValidationTestCase
{
    protected $formName = \Olcs\Form\Model\Form\Surrender\OperatorLicence::class;

    public function testLicenceDocument()
    {
        $element = ['operatorLicenceDocument', 'operatorLicenceDocument'];
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementType($element, Radio::class);
        $validValues = ['possession', 'lost', 'stolen'];
        foreach ($validValues as $validValue) {
            $this->assertFormElementValid($element, $validValue);
        }
        $invalidValues = ['one', 'two', 'three'];
        foreach ($invalidValues as $invalidValue) {
            $this->assertFormElementNotValid($element, $invalidValue, ['notInArray']);
        }
    }

    public function testPossessionContent()
    {
        $notice = ['operatorLicenceDocument', 'possessionContent', 'notice'];
        $this->assertFormElementAllowEmpty($notice, true);
        $this->assertFormElementType($notice, HtmlTranslated::class);
    }

    public function testLostContent()
    {
        $notice = ['operatorLicenceDocument', 'lostContent', 'notice'];
        $this->assertFormElementAllowEmpty($notice, true);
        $this->assertFormElementType($notice, HtmlTranslated::class);

        $details = ['operatorLicenceDocument', 'lostContent', 'details'];
        $this->assertFormElementAllowEmpty($details, true);
        $this->assertFormElementType($details, Textarea::class);
    }

    public function testLostContentValidLenght()
    {
        $form = $this->getForm();

        $data = [
            'operatorLicenceDocument' => [
                'operatorLicenceDocument' => 'lost',
                'lostContent' => ['details' => str_repeat('acbd ', 100)]
            ]
        ];
        $this->setPost($data);
        $form->setData($data);
        $valid = $form->isValid();

        $this->assertTrue($valid);

        $this->clearPost();
    }

    /**
     * @dataProvider dpTestLostContentNotValidLength
     */
    public function testLostContentNotValidLength($string)
    {
        $form = $this->getForm();

        $data = [
            'operatorLicenceDocument' => [
                'operatorLicenceDocument' => 'lost',
                'lostContent' => ['details' => $string]
            ]
        ];
        $this->setPost($data);
        $form->setData($data);
        $valid = $form->isValid();

        $this->assertFalse($valid);

        $this->clearPost();
    }

    public function dpTestLostContentNotValidLength()
    {
        return [
            [
                'more_than_500' => str_repeat('acbd ', 101),

            ],
            [
                'empty_string' => ''
            ]
        ];
    }

    public function testStolenContent()
    {
        $notice = ['operatorLicenceDocument', 'stolenContent', 'notice'];
        $this->assertFormElementAllowEmpty($notice, true);
        $this->assertFormElementType($notice, HtmlTranslated::class);

        $details = ['operatorLicenceDocument', 'stolenContent', 'details'];
        $this->assertFormElementAllowEmpty($details, true);
        $this->assertFormElementType($details, Textarea::class);
    }

    public function testStolenContentValidLength()
    {
        $form = $this->getForm();

        $data = [
            'operatorLicenceDocument' => [
                'operatorLicenceDocument' => 'stolen',
                'stolenContent' => ['details' => str_repeat('acbd ', 100)]
            ]
        ];
        $this->setPost($data);
        $form->setData($data);
        $valid = $form->isValid();

        $this->assertTrue($valid);

        $this->clearPost();
    }

    public function testStolenContentNotValidLength()
    {
        $form = $this->getForm();

        $data = [
            'operatorLicenceDocument' => [
                'operatorLicenceDocument' => 'stolen',
                'stolenContent' => ['details' => str_repeat('acbd ', 101)]
            ]
        ];
        $this->setPost($data);
        $form->setData($data);
        $valid = $form->isValid();

        $this->assertFalse($valid);

        $this->clearPost();
    }

    public function testFormActions()
    {
        $notice = ['form-actions', 'submit'];
        $this->assertFormElementType($notice, ActionButton::class);
    }

    private function setPost(array $data)
    {
        $_POST = $data;
    }

    private function clearPost()
    {
        $_POST = [];
    }
}
