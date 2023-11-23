<?php

$environment = $_ENV['APP_ENV'] ?? 'development';

$isProduction = $environment === 'production';

return [
    'version' => [
        'environment' => '%env%',
        'release' => (file_exists(__DIR__ . '/../version') ? file_get_contents(__DIR__ . '/../version') : ''),
        'description' => '%domain%',
    ],
    'api_router' => [
        'routes' => [
            'api' => [
                'child_routes' => [
                    'backend' => [
                        'options' => [
                            // Backend service URI *Environment specific*
                            'route' => 'api.%domain%'
                        ]
                    ]
                ]
            ]
        ]
    ],

    // Service addresses
    'service_api_mapping' => [
        'endpoints' => [
            // Backend service URI *Environment specific*
            'backend' => [
                'url' => 'http://api.%domain%/',
                'options' => [
                    'adapter' => \Laminas\Http\Client\Adapter\Curl::class,
                    'timeout' => 60,
                ]
            ],
            // Postcode/Address service URI *Environment specific*
            'postcode' => [
                'url' => 'http://address.%domain%/',
                'options' => [
                    'adapter' => \Laminas\Http\Client\Adapter\Curl::class,
                    'timeout' => 60,
                ]
            ],
        ]
    ],

    // Asset path, URI to olcs-static (CSS, JS, etc] *Environment specific*
    'asset_path' => '/static/public',

    'openam' => [
        'url' => 'http://ssauth.%domain%:8080/secure/',
        'realm' => 'selfserve',//@deprecated
        'cookie' => [
            'domain' => '%olcs_ss_cookie%',
        ]
    ],
    'cookie-manager' => [
        'delete-undefined-cookies' => true,
        'user-preference-cookie-name' => 'cookie_policy',
        'user-preference-cookie-secure' => true,
        'user-preference-cookie-expiry-days' => 365,
        'user-preference-configuration-form-id' => 'olcs-cookie-settings',
        'cookie-banner-id' => 'olcs-cookie-banner',
        'user-preference-saved-callback' => false,
        'cookie-banner-visibility-class' => 'hidden',
        'cookie-banner-visible-on-page-with-preference-form' => false,
        'cookie-manifest' =>
            [
                [
                    'category-name' => 'essential',
                    'optional' => false,
                    'cookies' =>
                        [
                            'PHPSESSID',
                            'secureToken',
                            'incap_ses',
                            'nlbi_',
                            'visid_incap',
                            '_utmvm',
                            '_ utmva',
                            '__utmvb',
                            '__utmvc'
                        ],
                ],
                [
                    'category-name' => 'analytics',
                    'optional' => true,
                    'cookies' =>
                        [
                            '_ga',
                            '_gtm',
                            '_gid',
                            '_gat',
                            '__utmt'
                        ],
                ],
                [
                    'category-name' => 'settings',
                    'optional' => true,
                    'cookies' =>
                        [
                            'langPref',
                        ],
                ],
            ],
    ],

    /**
     * Configure the location of the application log
     */
     'log' => [
         'Logger' => [
             'writers' => [
                 'full' => [
                     'options' => [
                         'stream' => 'php://stdout'
                     ],
                 ]
             ]
         ],
         'ExceptionLogger' => [
             'writers' => [
                 'full' => [
                     'options' => [
                         'stream' => 'php://stderr'
                     ],
                 ]
             ]
         ]
     ],

	  // enable the virus scanning of uploaded files
    // To disable scanning comment out this section or set 'cliCommand' to ""
    'antiVirus' => [
        'cliCommand' => 'clamdscan --no-summary --remove %%s',
    ],

    // Google Tag Manager id. If empty or not exists (commented out), then no Google Tag Manager code will be rendered
    'google-tag' => [
        'id' => '%olcs_google_id%',
        'gtm_auth' => '%olcs_google_gtm_auth%',
        'gtm_preview' => '%olcs_google_gtm_preview%',
    ],

    // The domain value needed to delete GA cookies
    'google-ga-domain' => '.ssweb.%domain%',

    'cache-encryption' => [
        'node_suffix' => 'ssweb',
        'adapter' => '%cache_encryption_adapter%',
        'options' => [
            'algo' => '%cache_encryption_algo%',
            'mode' => '%cache_encryption_mode%',
        ],
        'secrets' => [
            'node' => '%cache_encryption_secret_ss%',
            'shared' => '%cache_encryption_secret_shared%',
        ],
    ],

    'query_cache' => [
        // whether the cqrs cache is enabled
        'enabled' => '%cqrs_cache_enabled%',
        //sets the ttl for cqrs cache - note that these caches are also used by internal
        'ttl' => [
            \Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface::class => '%cqrs_cache_medium_ttl%',
            \Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface::class => '%cqrs_cache_long_ttl%',
        ],
    ],

    'caches' => [
        \Laminas\Cache\Storage\Adapter\Redis::class => [
            'adapter' => [
                'name' => 'redis',
                'options' => [
                    'server' => [
                        'host' => '%redis_cache_fqdn%',
                        'port' => 6379,
                    ],
                    'lib_options' => [
                        \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_IGBINARY
                    ],
                    'ttl' => 3600, //one hour, likely to be overridden based on use case
                    'namespace' => 'zfcache',
                ],
                'plugins' => [
                    'exception_handler' => [
                        'throw_exceptions' => false,
                    ],
                ],
            ],
        ],
    ],

    'html-purifier-cache-dir' => 'data/cache/htmlPurifier',

    'verify' => [
        'forwarder' => [
            'valid-origin' => '%verify_forwarder_valid_origin%'
        ]
    ],

    'session-timeout-warning-modal-helper' => [
        'enabled' => true,
        'seconds-before-expiry-warning' => 300,  // 5 minutes
        'timeout-redirect-url' => '/auth/timeout',
    ],

    'auth' => [
        'user_unique_id_salt' => '%user_unique_id_salt%',
        'realm' => 'selfserve',
        'session_name' => 'Identity',
        'identity_provider' => \Common\Rbac\JWTIdentityProvider::class
    ],

    'govukaccount-redirect' => [
        //Whitelist of GET keys allowed to be passed-thru to the self-serve controller URI
        'get-whitelist' => [
            'state',
            'code',
            'error',
            'error_description'
        ],
        // URI Path on SelfServe to redirect inbound visitors to
        'redirect-path' => '/govuk-account/process',
        // HTTP_REFERER hostname will be checked to end with the following string.
        'referrer_ends_with' => '.gov.uk'
    ]
];