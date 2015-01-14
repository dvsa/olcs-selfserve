<?php

/**
 * External Controller Trait Stub
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits\Stubs;

use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * External Controller Trait Stub
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ExternalControllerTraitStub extends AbstractActionController
{
    use ExternalControllerTrait;

    public function callRender($title, $form = null, $variables = array(), $sectionName = null)
    {
        return $this->render($title, $form, $variables, $sectionName);
    }

    /**
     * @param string $currentSection
     * @return array
     */
    protected function getSectionStepProgress($currentSection)
    {
        // stubbed for test purposes
        return ['stepX' => 2, 'stepY' => 12];
    }
}
