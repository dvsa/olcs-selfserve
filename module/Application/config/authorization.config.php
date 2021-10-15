<?php

return [
    'guards' => [
        'ZfcRbac\Guard\RoutePermissionsGuard' => [
            'lva-application/transport_manager*' => ['selfserve-tm'],
            'lva-application' => ['selfserve-lva'],
            'lva-application/*' => ['selfserve-lva'],
            'create_application' => ['selfserve-user'],
        ],
    ],
];