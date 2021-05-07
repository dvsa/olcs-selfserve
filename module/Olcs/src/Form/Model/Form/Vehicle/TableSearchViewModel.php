<?php

namespace Olcs\Form\Model\Form\Vehicle;

use Laminas\View\Model\ViewModel;

class TableSearchViewModel extends ViewModel
{
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        $form = new TableSearchFormElement('vehicle-search', [
            'label' => 'licence.vehicle.table.search.label',
            'legend' => 'licence.vehicle.table.search.list.legend',
            'action-label' => 'licence.vehicle.table.search.button',
        ]);
        $searchFormData = array_intersect_key($variables['input'], array_flip([TableSearchFormElement::FIELD_SEARCH]));
        if (! empty($searchFormData)) {
            $form->setData($searchFormData);
            $form->isValid();
        }
        $form->prepare();
        $this->setVariable('form', $form);
        $this->setVariable('query', array_diff_key($variables['input'], array_flip([TableSearchFormElement::FIELD_SEARCH])));

        $this->setTemplate('foo');
    }
}
