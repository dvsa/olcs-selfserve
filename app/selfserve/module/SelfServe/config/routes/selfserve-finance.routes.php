<?php

return [
    'finance' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/finance[/]',
            'defaults' => array(
                'controller' => 'Selfserve\Finance\Index',
                'action' => 'index'
            )
        ),
        'may_terminate' => false,
        'child_routes' => array(
            'operating_centre' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'operating-centre[/:action][/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'SelfServe\Finance\OperatingCentreController',
                        'action' => 'index'
                    )
                )
            ),
            'financial_evidence' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'financial-evidence',
                    'defaults' => array(
                        'controller' => 'SelfServe\Finance\FinancialEvidenceController',
                        'action' => 'index'
                    )
                )
            )
        )
    )
];
