<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LgvUndertakings;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * LGV Undertakings Form Test
 */
class LgvUndertakingsTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var LgvUndertakings
     */
    protected $sut;

    protected $formHelper;

    protected $formName = 'Lva\LgvUndertakings';

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new LgvUndertakings();
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testGetForm()
    {
        $form = m::mock(Form::class);

        $this->formHelper
            ->shouldReceive('createForm')
            ->once()
            ->with($this->formName)
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper);

        $actual = $this->sut->getForm();

        $this->assertSame($form, $actual);
    }
}
