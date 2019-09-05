<?php

use Common\Util\Escape;

return array(
    'variables' => array(),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            ),
        )
    ),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'permits.ecmt.page.unpaid.tableheader.permit',
            'name' => 'permitNumber',
            'formatter' => function ($row) {
                return '<b>' . Escape::html($row['permitNumber']) . '</b>';
            },
        ),
        array(
            'title' => 'permits.ecmt.page.unpaid.tableheader.min-emission',
            'name' => 'emissionsCategory',
            'formatter' => 'RefData',
        ),
        array(
            'title' => 'permits.ecmt.page.unpaid.tableheader.countries',
            'name' => 'countries',
            'formatter' => function ($row, $column, $sm) {
                $translator = $sm->get('translator');
                if (count($row['countries']) === 0) {
                    return $translator->translate('permits.ecmt.page.unpaid.no.countries');
                }
                $rc = [];
                foreach ($row['countries'] as $country) {
                    $rc[] = $translator->translate($country['countryDesc']);
                }
                return Escape::html(implode(', ', $rc));
            }
        )
    )
);
