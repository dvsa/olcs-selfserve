<?php

use Common\Service\Table\Formatter\ValidityPeriod;
use Common\Util\Escape;
use Common\View\Helper\CurrencyFormatter;

return [
    'variables' => [],
    'settings' => [
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'permits.irhp.fee-breakdown.year',
            'isNumeric' => true,
            'name' => 'year',
        ],
        [
            'title' => 'permits.irhp.fee-breakdown.validity-period',
            'name' => 'validityPeriod',
            'formatter' => ValidityPeriod::class,
        ],
        [
            'title' => 'permits.irhp.fee-breakdown.fee-per-permit',
            'isNumeric' => true,
            'name' => 'feePerPermit',
            'formatter' => function ($row, $column) {
                return (new CurrencyFormatter())($row['feePerPermit']);
            }
        ],
        [
            'title' => 'permits.irhp.fee-breakdown.number-of-permits',
            'isNumeric' => true,
            'name' => 'numberOfPermits',
        ],
        [
            'title' => 'permits.irhp.fee-breakdown.total-fee',
            'isNumeric' => true,
            'name' => 'totalFee',
            'formatter' => function ($row, $column) {
                return (new CurrencyFormatter())($row['totalFee']);
            }
        ],
    ]
];
