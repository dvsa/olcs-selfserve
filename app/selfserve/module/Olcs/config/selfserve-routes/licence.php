<?php

use Olcs\Controller\Licence\Surrender\ReviewContactDetailsController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    [
        'licence' => [
            'type' => Segment::class,
            'options' => [
                'route' => '/licence/:licence[/]',
                'constraints' => [
                    'licence' => '[0-9]+',
                ],
            ],
            'may_terminate' => false,
            'child_routes' => [
                'surrender' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'surrender[/]',

                    ],
                    'may_terminate' => false,
                    'child_routes' => [
                        'start' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'start[/]',
                                'defaults' => [
                                    'controller' => 'SurrenderStart',
                                    'action' => 'index',
                                ],
                            ],
                        ],
                        'review-contact-details' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':surrender/review-contact-details[/:action][/]',
                                'defaults' => [
                                    'controller' => ReviewContactDetailsController::class,
                                ],
                            ],
                        ],
                        'address-details' => [
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'address-details[/]',
                                'defaults' => [
                                    'controller' => 'AddressDetails',
                                    'action' => 'index',
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ],
    ]
];
