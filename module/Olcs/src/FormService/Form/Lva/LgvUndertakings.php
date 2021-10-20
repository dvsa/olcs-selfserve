<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;
use Laminas\Form\FormInterface;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * LGV Undertakings Form
 */
class LgvUndertakings extends AbstractFormService
{
    use ButtonsAlterations;

    /**
     * Get Form
     *
     * @return FormInterface
     */
    public function getForm()
    {
        $form = $this->getFormHelper()->createForm('Lva\LgvUndertakings');

        $this->alterForm($form);

        return $form;
    }

    /**
     * Make form alterations
     *
     * @param FormInterface $form form
     *
     * @return void
     */
    private function alterForm($form)
    {
        $this->alterButtons($form);
    }
}
