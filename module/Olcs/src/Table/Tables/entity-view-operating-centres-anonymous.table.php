<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'search-result-label-operating-centre',
            'addressFields' => 'FULL',
            'formatter' => 'Address',
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'search-result-label-vehicles-hgv',
            'name' => 'noOfHgvVehiclesRequired',
            'formatter' => function ($data, $column) {
                if (empty($data['noOfHgvVehiclesRequired'])) {
                    return '0';
                }
                return $data['noOfHgvVehiclesRequired'];
            }
        ),
        array(
            'title' => 'search-result-label-vehicles-lgv',
            'name' => 'noOfLgvVehiclesRequired',
            'formatter' => 'OcNoOfLgvVehiclesRequired'
        ),
        array(
            'title' => 'search-result-label-trailers',
            'formatter' => function ($data, $column) {
                if (empty($data['noOfTrailersRequired'])) {
                    return '0';
                }
                return $data['noOfTrailersRequired'];
            }
        )
    )
);
