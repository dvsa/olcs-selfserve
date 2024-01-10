<?php

namespace OlcsTest\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\FormServiceManager;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Translator\TranslationLoader;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\People\SoleTrader\VariationSoleTrader as Sut;
use Laminas\Form\Form;
use ZfcRbac\Service\AuthorizationService;

/**
 * Variation Sole Trader Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationSoleTraderTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $fsm;

    protected $sm;

    protected $mockVariationService;

    public function setUp(): void
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->mockVariationService = m::mock(Form::class);

        /** @var FormServiceManager fsm */
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->peopleLvaService = m::mock(PeopleLvaService::class);

        $this->fsm->setService('lva-variation', $this->mockVariationService);

        $this->sut = new Sut($this->formHelper, m::mock(AuthorizationService::class), $this->peopleLvaService, $this->fsm);
    }

    /**
     * @dataProvider noDisqualifyProvider
     */
    public function testGetFormNoDisqualify($params)
    {
        $params['canModify'] = true;

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('has')->with('disqualify')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('disqualify');

        $form = m::mock(Form::class);

        $this->mockVariationService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->sut->getForm($params);
    }

    public function testGetForm()
    {
        $params = [
            'location' => 'internal',
            'personId' => 123,
            'isDisqualified' => false,
            'canModify' => true,
            'disqualifyUrl' => 'foo'
        ];

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('get->setValue')
            ->once()
            ->with('foo');

        $form = m::mock(Form::class);

        $this->mockVariationService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->sut->getForm($params);
    }

    public function testGetFormCantModify()
    {
        $params = [
            'location' => 'internal',
            'personId' => 123,
            'isDisqualified' => false,
            'canModify' => false,
            'disqualifyUrl' => 'foo',
            'orgType' => 'bar'
        ];

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('get->setValue')
            ->once()
            ->with('foo');

        $form = m::mock(Form::class);

        $this->mockVariationService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\SoleTrader')
            ->andReturn($form);

        $this->peopleLvaService->shouldReceive('lockPersonForm')
            ->once()
            ->with($form, 'bar');

        $this->sut->getForm($params);
    }

    public function noDisqualifyProvider()
    {
        return [
            [
                ['location' => 'external']
            ],
            [
                [
                    'location' => 'internal',
                    'personId' => null
                ]
            ],
            [
                [
                    'location' => 'internal',
                    'personId' => 123,
                    'isDisqualified' => true
                ]
            ],
        ];
    }
}
