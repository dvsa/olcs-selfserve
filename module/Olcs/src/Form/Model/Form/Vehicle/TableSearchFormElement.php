<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Vehicle;

use Common\Form\Elements\Types\TableSearch;
use Laminas\Form\Form;
use Laminas\Form\Element\Hidden;

class TableSearchFormElement extends Form
{
    const FIELD_SEARCH = 's';

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $searchInputElement = new TableSearch('table-search');

        if (isset($options['action-label'])) {
            $searchInputElement->setActionLabel($options['action-label']);
        }

        if (isset($options['hint'])) {
            $searchInputElement->setHint($options['hint']);
        }

        if (isset($options['label'])) {
            $searchInputElement->setLabel($options['label']);
        }

        if (isset($options['legend'])) {
            $searchInputElement->setLegend($options['legend']);
        }

        $this->add($searchInputElement);

        $this->setAttribute('class', 'filters form__search');
        $this->setAttribute('method', 'get');

        // @todo add input filter for validation
    }

    public function setData($data)
    {
        // @todo move this logic to a parent "GET" form class

        // Add hidden fields for any other data that is not search related
        $extraFormData = array_diff_key($data, array_flip([static::FIELD_SEARCH]));
        while (! empty($extraFormData)) {
            end($extraFormData);
            $key = key($extraFormData);
            $value = array_pop($extraFormData);
            if (is_array($value)) {
                foreach ($value as $arrayItemKey => $arrayItemValue) {
                    $extraFormData[sprintf('%s[%s]', $key, $arrayItemKey)] = $arrayItemValue;
                }
                continue;
            }
            $hiddenInputElement = new Hidden();
            $hiddenInputElement->setAttribute('name', $key);
            $hiddenInputElement->setAttribute('value', $value);
            $this->add($hiddenInputElement);
        }

        return parent::setData($data);
    }
}
