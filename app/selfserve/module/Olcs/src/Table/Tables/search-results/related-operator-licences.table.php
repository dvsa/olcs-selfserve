<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'search-result-label-lic-no',
            'formatter' => function ($data) {
                if (isset($data['id'])) {
                    return '<a href="' . $this->generateUrl(
                        array('entity' => 'licence', 'entityId' => $data['id']),
                        'search-result',
                        false
                    ) . '">' . $data['licNo'] . '</a>';
                }
                return '';
            }
        ),
        array(
            'title' => 'search-result-label-licence-status',
            'formatter' => 'RefData',
            'name' => 'status'
        ),
        array(
            'title' => 'search-result-label-continuation-date',
            'formatter' => 'Date',
            'name' => 'expiryDate'
        )
    )
);
