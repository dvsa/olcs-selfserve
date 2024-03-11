<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\StackValue;
use Common\Util\Escape;

return [
    'variables' => [],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ],
        ],
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'permits.irhp.unpaid.permits.table.permit',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => function ($row) {
                return '<b>' . Escape::html($row['permitNumber']) . '</b>';
            },
        ],
        [
            'title' => 'permits.irhp.unpaid.permits.table.min-emission',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'permits.irhp.unpaid.permits.table.countries',
            'name' => 'constrainedCountries',
            'formatter' => ConstrainedCountriesList::class,
        ],
    ]
];
