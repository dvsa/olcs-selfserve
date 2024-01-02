<?php

namespace OlcsTest\Form\Model\Form\Lva;

use OlcsTest\TestHelpers\AbstractFormValidationTestCase;

/**
 * Class ApplicationUndertakingsTest
 *
 * @group FormTests
 */
class ApplicationUndertakingsTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Olcs\Form\Model\Form\Lva\ApplicationUndertakings::class;

    public function testReview()
    {
        $element = ['declarationsAndUndertakings', 'review'];
        $this->assertFormElementHtml($element);
    }

    public function testDeclaration()
    {
        $element = ['declarationsAndUndertakings', 'declaration'];
        $this->assertFormElementHtml($element);
    }

    public function testSignatureOptions()
    {
        $element = ['declarationsAndUndertakings', 'signatureOptions'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testDisabledReview()
    {
        $element = ['declarationsAndUndertakings', 'disabledReview'];
        $this->assertFormElementHtml($element);
    }

    public function testDeclarationDownload()
    {
        $element = ['declarationsAndUndertakings', 'declarationDownload'];
        $this->assertFormElementHtml($element);
    }

    public function testDeclarationForVerify()
    {
        $element = ['declarationsAndUndertakings', 'declarationForVerify'];
        $this->assertFormElementHtml($element);
    }

    public function testVersion()
    {
        $element = ['declarationsAndUndertakings', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testId()
    {
        $element = ['declarationsAndUndertakings', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testGoodsApplicationInterim()
    {
        $element = ['interim', 'goodsApplicationInterim'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementValid($element, 'Y');
        $this->assertFormElementValid($element, 'N');
    }

    public function testGoodsApplicationInterimReason()
    {
        $element = ['interim','YContent','goodsApplicationInterimReason'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementText($element);
        $this->assertFormElementAllowEmpty(['interim','YContent','goodsApplicationInterimReason'], true);
    }

    public function testDeclarationsAndUndertakingsInterimInterimGuidanceText()
    {
        $this->assertFormElementHtml(['interim', 'YContent', 'interimGuidanceText']);
    }

    public function testSign()
    {
        $element = ['form-actions', 'sign'];
        $this->assertFormElementActionButton($element);
    }

    public function testSubmitAndPay()
    {
        $element = ['form-actions', 'submitAndPay'];
        $this->assertFormElementActionButton($element);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testChange()
    {
        $element = ['form-actions', 'change'];
        $this->assertFormElementActionButton($element);
    }

    public function testSaveAndContinue()
    {
        $element = ['form-actions', 'saveAndContinue'];
        $this->assertFormElementActionButton($element);
    }

    public function testSave()
    {
        $element = ['form-actions', 'save'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }

    public function testInterimFee()
    {
        $element = ['interim', 'YContent', 'interimFee'];
        $this->assertFormElementIsRequired($element, false);
        $this->assertFormElementHtml($element);
    }
}
