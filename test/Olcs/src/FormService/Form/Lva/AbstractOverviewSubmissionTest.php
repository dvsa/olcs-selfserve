<?php

namespace OlcsTest\FormService\Form\Lva;

use Laminas\I18n\View\Helper\Translate;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use OlcsTest\FormService\Form\Lva\Stub\AbstractOverviewSubmissionStub;

/**
 * @covers Olcs\FormService\Form\Lva\AbstractOverviewSubmission
 */
class AbstractOverviewSubmissionTest extends MockeryTestCase
{
    /** @var  AbstractOverviewSubmissionStub */
    private $sut;

    /** @var  m\MockInterface */
    private $mockSm;
    /** @var  m\MockInterface | \Laminas\Form\FormInterface */
    private $mockForm;
    /** @var  m\MockInterface | \Common\Service\Helper\FormHelperService */
    private $mockFormHlp;

    private $mockTranslationHelper;

    public function setUp(): void
    {
        $this->mockForm = m::mock(\Laminas\Form\FormInterface::class);

        $this->mockSm = Bootstrap::getServiceManager();

        $this->mockTranslationHelper = m::mock(Translate::class);
        $this->mockTranslationHelper
            ->shouldReceive('translateReplace')
            ->andReturnUsing(
                function ($text, $params) {
                    return '_TRLTD_' . $text . '[' . implode('|', $params) . ']';
                }
            );

        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();

        $this->sut = new AbstractOverviewSubmissionStub($this->mockTranslationHelper);
        $this->sut->setFormHelper($this->mockFormHlp);
    }

    public function testGetForm()
    {
        $data = ['data'];
        $params = [
            'sections' => ['unit_Sections'],
        ];

        $this->mockFormHlp
            ->shouldReceive('createForm')->once()->with('Lva\PaymentSubmission')->andReturn($this->mockForm);

        /** @var AbstractOverviewSubmissionStub | m\MockInterface $sut */
        $sut = m::mock(AbstractOverviewSubmissionStub::class . '[alterForm]', [$this->mockTranslationHelper])
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('alterForm')->once()->with($this->mockForm, $data, $params)
            ->getMock();

        $sut->setFormHelper($this->mockFormHlp);

        static::assertSame($this->mockForm, $sut->getForm($data, $params));
    }

    public function testAlterFormReadySubmitWithFee()
    {
        $data = [
            'outstandingFeeTotal' => 999,
            'disableCardPayments' => true,
        ];
        $params = [
            'actionUrl' => 'unit_ActionUrl',
            'isReadyToSubmit' => true,
        ];

        //  expect
        $mockAmountElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setValue')->once()->with('_TRLTD_application.payment-submission.amount.value[999.00]')
            ->getMock();

        $mocksubmitPayElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setLabel')->once()->with('submit-application.button')
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')->once()->with('amount')->andReturn($mockAmountElm)
            ->shouldReceive('get')->once()->with('submitPay')->andReturn($mocksubmitPayElm)
            ->shouldReceive('setAttribute')->once()->with('action', 'unit_ActionUrl');

        //  call
        $this->sut->alterForm($this->mockForm, $data, $params);
    }

    public function testAlterFormNotReadyNoFee()
    {
        $data = [
            'outstandingFeeTotal' => -1,
            'disableCardPayments' => false,
        ];
        $params = [
            'isReadyToSubmit' => false,
        ];

        //  expect
        $mocksubmitPayElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setLabel')->once()->with('submit-application.button')
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')->never()->with('amount')
            ->shouldReceive('get')->once()->with('submitPay')->andReturn($mocksubmitPayElm);

        $this->mockFormHlp
            ->shouldReceive('remove')->once()->with($this->mockForm, 'amount')
            ->shouldReceive('remove')->once()->with($this->mockForm, 'submitPay');

        //  call
        $this->sut->alterForm($this->mockForm, $data, $params);
    }

    public function testSectionsWithStatus()
    {
        $params = [
            'sections' => [
                [
                    'status' => 'unit_ExpectedStatus',
                ],
            ],
        ];

        /** @var AbstractOverviewSubmissionStub | m\MockInterface $sut */
        $sut = m::mock(AbstractOverviewSubmissionStub::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('getFormHelper->createForm')->andReturn($this->mockForm);
        $sut->shouldReceive('alterForm');

        $sut->getForm([], $params);

        static::assertTrue($sut->hasSectionsWithStatus('unit_ExpectedStatus'));
        static::assertFalse($sut->hasSectionsWithStatus('unit_NotExpectedStatus'));
    }
}
