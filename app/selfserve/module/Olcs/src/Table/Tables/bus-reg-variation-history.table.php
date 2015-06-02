<?php

$variationNo = 1;
return array(
    'variables' => array(
        'title' => 'Registration history'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Reg No.',
            'formatter' => function ($data) {
                if (isset($data['id'])) {
                    return '<a href="' . $this->generateUrl(
                        array('action' => 'details', 'busRegId' => $data['id']),
                        'bus-registration',
                        false
                    ) . '">' . $data['regNo'] . '</a>';
                }
                return '';
            }
        ),
        array(
            'title' => 'Var No.',
            'name' => 'variationNo'
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data) {
                return $data['status']['description'];
            }
        ),
        array(
            'title' => 'Application type',
            'formatter' => function ($data, $column, $sm) {
                if ($data['isTxcApp'] == 'Y') {
                    if ($data['ebsrRefresh'] == 'Y') {
                        return $sm->get('translator')->translate('EBSR Data Refresh');
                    } else {
                        return $sm->get('translator')->translate('EBSR');
                    }
                } else {
                    return $sm->get('translator')->translate('Manual');
                }
            }
        ),
        array(
            'title' => 'Date received',
            'formatter' => 'Date',
            'name' => 'receivedDate'
        ),
        array(
            'title' => 'Date effective',
            'formatter' => 'Date',
            'name' => 'effectiveDate'
        ),
        array(
            'title' => 'End date',
            'formatter' => 'Date',
            'name' => 'endDate'
        ),
        array(
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'formatter' => function ($data) {
                if (isset($data['canDelete'])) {
                    return '<input type="radio" name="id" value="' . $data['id'] . '">';
                }
            },
        )
    )
);
