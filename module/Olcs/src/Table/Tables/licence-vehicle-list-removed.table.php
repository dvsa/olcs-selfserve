<?php

return [
    'paginate' => [
        'limit' => [
            'default' => \Olcs\Controller\Licence\Vehicle\ListVehicleController::DEFAULT_REMOVED_VEHICLES_TABLE_LIMIT,
            'options' => [10],
        ],
    ],
    'columns' => [
        [
            'title' => 'table.licence-vehicle-list-removed.column.vrm.title',
            'formatter' => 'VehicleLink',
            'sort' => 'v.vrm',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.weight.title',
            'stack' => 'vehicle->platedWeight',
            'formatter' => 'NumberStackValue',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.specified.title',
            'formatter' => 'Date',
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate',
        ],
        [
            'title' => 'table.licence-vehicle-list-removed.column.removed.title',
            'formatter' => 'Date',
            'name' => 'removalDate',
            'sort' => 'removalDate',
        ],
    ],
];
