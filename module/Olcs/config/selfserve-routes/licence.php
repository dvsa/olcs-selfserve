<?php

use Olcs\Controller\Licence\Surrender\ReviewContactDetailsController;
use Olcs\Controller\Licence\Surrender\StartController;
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
                            'may_terminate' => false,
                            'type' => Segment::class,
                            'options' => [
                                'route' => 'start[/]',
                            ],
                            'child_routes' => [
                                'GET' => [
                                    'may_terminate' => true,
                                    'type' => \Zend\Mvc\Router\Http\Method::class,
                                    'options' => [
                                        'verb'=>'GET',
                                        'defaults' => [
                                            'controller' => StartController::class,
                                            'action' => 'index',
                                        ],
                                    ],
                                ],
                                'POST' => [
                                    'may_terminate' => true,
                                    'type' => \Zend\Mvc\Router\Http\Method::class,
                                    'options' => [
                                        'verb'=>'POST',
                                        'defaults' => [
                                            'controller' => StartController::class,
                                            'action' => 'start',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'review-contact-details' => [
                            'may_terminate' => true,
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':surrender/review-contact-details[/:action][/]',
                                'defaults' => [
                                    'controller' => ReviewContactDetailsController::class,
                                    'action' => 'index',
                                ],
                            ],
                        ],
                        'address-details' => [
                            'may_terminate' => true,
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':surrender/address-details[/]',
                                'defaults' => [
                                    'controller' => Olcs\Controller\Licence\Surrender\AddressDetailsController::class,
                                    'action' => 'index',
                                ],
                            ],
                        ],
                        'operator' => [
                            'may_terminate' => true,
                            'type' => Segment::class,
                            'options' => [
                                'route' => ':surrender/operator[/]',
                                'defaults' => [
                                    'controller' => Olcs\Controller\Licence\Surrender\OperatorController::class,
                                    'action' => 'index',
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ],
    ],
];
