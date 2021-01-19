<?php

namespace Olcs\Service\Qa;

use Common\Service\Gds\FormUpdater;
use Common\Service\Qa\FieldsetPopulator;
use Common\Service\Qa\UsageContext;

class FormProvider
{
    /** @var FormFactory */
    private $formFactory;

    /** @var FieldsetPopulator */
    private $fieldsetPopulator;

    /** @var FormUpdater */
    private $formUpdater;

    /**
     * Create service instance
     *
     * @param FormFactory $formFactory
     * @param FieldsetPopulator $fieldsetPopulator
     * @param FormUpdater $formUpdater
     *
     * @return FormProvider
     */
    public function __construct(FormFactory $formFactory, FieldsetPopulator $fieldsetPopulator, FormUpdater $formUpdater)
    {
        $this->formFactory = $formFactory;
        $this->fieldsetPopulator = $fieldsetPopulator;
        $this->formUpdater = $formUpdater;
    }

    /**
     * Get a Form instance corresponding to the supplied form data
     *
     * @param array $options
     * @param string $formName
     *
     * @return mixed
     */
    public function get(array $options, $formName)
    {
        $form = $this->formFactory->create($formName);
        $form->setApplicationStep($options);
        $this->fieldsetPopulator->populate($form, [$options], UsageContext::CONTEXT_SELFSERVE);
        $this->formUpdater->update($form);
        return $form;
    }
}
