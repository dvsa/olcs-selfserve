<?php

use Dvsa\LaminasConfigCloudParameters\Cast\Boolean;
use Dvsa\LaminasConfigCloudParameters\ParameterProvider\Aws\SecretsManager;
use Dvsa\LaminasConfigCloudParameters\ParameterProvider\Aws\ParameterStore;

var_dump(\Aws\Credentials\CredentialProvider::defaultProvider());
var_dump(phpinfo());

return [
    'aws' => [
        'global' => [
            'http'    => [
                'connect_timeout' => 5,
                'timeout'         => 5,
            ],
        ],
    ],
    'config_parameters' => [
        'providers' => [
            SecretsManager::class => [
                // Todo: will need to be parameterised once all the terraform is ready.
                'DEVAPPDA-BASE-SM-APPLICATION-SECRETS',
            ],
            ParameterStore::class => [
                '/applicationparams/da/',
            ],
        ],
        'casts' => [
            '[query_cache][enabled]' => Boolean::class,
        ]
    ],
];