<?php

namespace OlcsTest\Service\Qa;

use Common\Service\Qa\FieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\FormProvider;
use Olcs\Service\Qa\FormFactory;
use Zend\Form\Form;

class FormProviderTest extends MockeryTestCase
{
    public function testGet()
    {
        $options = [
            'option1Key' => 'option1Value',
            'option2Key' => 'option2Value'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setApplicationStep')
            ->with($options)
            ->once();

        $formFactory = m::mock(FormFactory::class);
        $formFactory->shouldReceive('create')
            ->withNoArgs()
            ->once()
            ->andReturn($form);

        $fieldsetPopulator = m::mock(FieldsetPopulator::class);
        $fieldsetPopulator->shouldReceive('populate')
            ->with($form, [$options])
            ->once();

        $sut = new FormProvider($formFactory, $fieldsetPopulator);

        $this->assertSame(
            $form,
            $sut->get($options)
        );
    }
}
