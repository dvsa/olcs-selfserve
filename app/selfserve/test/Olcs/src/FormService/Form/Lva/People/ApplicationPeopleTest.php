<?php

namespace OlcsTest\FormService\Form\Lva\People;

use Common\Form\Elements\InputFilters\Lva\BackToApplicationActionLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\People\ApplicationPeople as Sut;
use Zend\Form\Form;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application People Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeopleTest extends MockeryTestCase
{
    use ButtonsAlterations;

    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new Sut();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testGetForm()
    {
        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');

        $form = m::mock();
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\People')
            ->andReturn($form);

        $this->mockAlterButtons($form, $this->formHelper, $formActions);

        $this->sut->getForm();
    }
}
