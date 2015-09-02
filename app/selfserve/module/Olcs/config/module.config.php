<?php

use Olcs\Controller\IndexController;
use Olcs\Controller\Search\SearchController;

use Olcs\Form\Element\SearchFilterFieldsetFactory;
use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchDateRangeFieldsetFactory;
use Olcs\Form\Element\SearchDateRangeFieldset;

use \Common\Service\Data\Search\SearchType;
use Common\Data\Object\Search\Licence as LicenceSearch;
use Common\Data\Object\Search\LicenceSelfserve as LicenceSelfserve;
use Common\Data\Object\Search\OperatingCentreSelfserve as OperatingCentreSearchIndex;
use Common\Data\Object\Search\PeopleSelfserve as PeopleSelfserveSearchIndex;

$sectionConfig = new \Common\Service\Data\SectionConfig();
$configRoutes = $sectionConfig->getAllRoutes();

$sections = $sectionConfig->getAllReferences();
$applicationDetailsPages = array();
$licenceDetailsPages = array();
$variationDetailsPages = array();

foreach ($sections as $section) {
    $applicationDetailsPages['application_' . $section] = array(
        'id' => 'application_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-application/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    );

    $licenceDetailsPages['licence_' . $section] = array(
        'id' => 'licence_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-licence/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    );

    $variationDetailsPages['variation_' . $section] = array(
        'id' => 'variation_' . $section,
        'label' => 'section.name.' . $section,
        'route' => 'lva-variation/' . $section,
        'params' => ['action' => 'index'],
        'use_route_match' => true
    );
}

$routes = array(
    'index' => array(
        'type' => 'literal',
        'options' =>  array(
            'route' => '/',
            'defaults' => array(
                'controller' => IndexController::class,
                'action' => 'index'
            )
        )
    ),
    'search' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/:index[/:action]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'operator'
            )
        )
    ),
    // Unfortunately, we need separate routes
    'search-operating-centre' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/operating-centre[/:action]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'operator'
            )
        )
    ),
    'search-person' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/person[/:action]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'person'
            )
        )
    ),
    'search-operator' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/operator[/:action]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'operator'
            )
        )
    ),
    'search-bus' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/bus[/:action]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'bus'
            )
        )
    ),
    'search-traffic-commissioner-publication' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/traffic-commissioner-publication[/:action]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'traffic-commissioner-publication'
            )
        )
    ),
    'search-vehicle-external' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/search/vehicle-external[/:action]',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'index',
                'index' => 'vehicle-external'
            )
        )
    ),
    'search-jump-home' => array(
        'type' => 'literal',
        'options' =>  array(
            'route' => '/search/jump',
            'defaults' => array(
                'controller' => SearchController::class,
                'action' => 'jump',
            )
        )
    ),
    'ebsr' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/ebsr[/:action]',
            'defaults' => array(
                'controller' => 'Olcs\Ebsr\Uploads',
                'action' => 'index'
            )
        )
    ),
    'bus-registration' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' =>
                '/bus-registration/:action[/busreg/:busRegId][/sub-type/:subType][/page/:page]' .
                '[/limit/:limit][/sort/:sort][/order/:order]',
            'defaults' => array(
                'controller' => 'Olcs\Ebsr\BusRegistration',
                'action' => 'index',
                'page' => 1,
                'limit' => 25,
                'sort' => 'submittedDate',
                'order' => 'DESC'
            )
        )
    ),
    'dashboard' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/dashboard[/]',
            'defaults' => array(
                'controller' => 'Dashboard',
                'action' => 'index'
            )
        )
    ),
    'fees' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/fees[/]',
            'defaults' => array(
                'controller' => 'Fees',
                'action' => 'index',
            ),
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'pay' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'pay/:fee[/]',
                    'constraints' => array(
                        'fee' => '[0-9\,]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Fees',
                        'action' => 'pay-fees',
                    ),
                ),
            ),
            'result' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'result[/]',
                    'defaults' => array(
                        'controller' => 'Fees',
                        'action' => 'handle-result',
                    ),
                ),
            ),
            'receipt' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'receipt/:reference[/:action]',
                    'constraints' => array(
                        'reference' => 'OLCS-[0-9A-F\-]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Fees',
                        'action' => 'receipt',
                    ),
                ),
            ),
        ),
    ),
    'correspondence' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/correspondence[/]',
            'defaults' => array(
                'controller' => 'Correspondence',
                'action' => 'index'
            )
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'access' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'access/:correspondenceId',
                    'defaults' => array(
                        'controller' => 'Correspondence',
                        'action' => 'accessCorrespondence'
                    ),
                )
            )
        )
    ),
    'create_application' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/application/create[/]',
            'defaults' => array(
                'skipPreDispatch' => true,
                'controller' => 'LvaApplication/TypeOfLicence',
                'action' => 'createApplication'
            )
        )
    ),
    'create_variation' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/variation/create/:licence[/]',
            'constraints' => array(
                'licence' => '[0-9]+',
            ),
            'defaults' => array(
                'controller' => 'LvaLicence',
                'action' => 'createVariation'
            )
        )
    ),
    'user' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/manage-user[/:action][/:id]',
            'defaults' => array(
                'controller' => 'User',
                'action' => 'index'
            )
        )
    ),
    'entity-view' => array(
        'type' => 'segment',
        'options' =>  array(
            'route' => '/view-details/:entity[/:entityId]',
            'constraints' => array(
                'entity' => '(licence)',
                'entityId' => '[0-9]+',
            ),
            'defaults' => array(
                'controller' => 'Entity\View',
                'action' => 'details'
            )
        )
    )
);

$configRoutes['lva-application']['child_routes'] = array_merge(
    $configRoutes['lva-application']['child_routes'],
    array(
        'review' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'review[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Review',
                    'action' => 'index'
                )
            )
        ),
        'payment' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'payment[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/PaymentSubmission',
                    'action' => 'index'
                )
            )
        ),
        'submission-summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'submission-summary[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Summary',
                    'action' => 'postSubmitSummary'
                )
            )
        ),
        'summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'summary[/:reference][/]',
                'constraints' => array(
                    'reference' => 'OLCS-[0-9A-F\-]+',
                ),
                'defaults' => array(
                    'controller' => 'LvaApplication/Summary',
                    'action' => 'index'
                )
            )
        ),
        'result' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'result[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/PaymentSubmission',
                    'action' => 'payment-result',

                )
            )
        )
    )
);

$configRoutes['lva-variation']['child_routes'] = array_merge(
    $configRoutes['lva-variation']['child_routes'],
    array(
        'review' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'review[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Review',
                    'action' => 'index'
                )
            )
        ),
        'submission-summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'submission-summary[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Summary',
                    'action' => 'postSubmitSummary'
                )
            )
        ),
        'summary' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'summary[/:reference][/]',
                'constraints' => array(
                    'reference' => 'OLCS-[0-9A-F\-]+',
                ),
                'defaults' => array(
                    'controller' => 'LvaVariation/Summary',
                    'action' => 'index'
                )
            )
        ),
        'payment' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'payment[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'index'
                )
            )
        ),
        'result' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'result[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/PaymentSubmission',
                    'action' => 'payment-result',

                )
            )
        ),
    )
);

$configRoutes['lva-licence']['child_routes'] = array_merge(
    $configRoutes['lva-licence']['child_routes'],
    array(
        'variation' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'variation[/:redirectRoute][/]',
                'defaults' => array(
                    'controller' => 'LvaLicence/Variation',
                    'action' => 'index'
                )
            )
        )
    )
);

foreach (['application', 'variation'] as $lva) {
    $configRoutes['lva-' . $lva]['child_routes'] = array_merge(
        $configRoutes['lva-' . $lva]['child_routes'],
        array(
            'transport_manager_details' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'transport-managers/details/:child_id[/]',
                    'constraints' => array(
                        'child_id' => '[0-9]+',
                        'grand_child_id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Lva' . ucfirst($lva) . '/TransportManagers',
                        'action' => 'details'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'action' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => ':action[/:grand_child_id][/]',
                            'constraints' => array(
                                'grand_child_id' => '[0-9\,]+'
                            ),
                            'defaults' => array(
                                'controller' => 'Lva' . ucfirst($lva) . '/TransportManagers'
                            )
                        )
                    )
                )
            )
        )
    );

    ${$lva . 'DetailsPages'}[$lva . '_transport_managers']['pages'] = [
        [
            'id' => $lva . '_transport_managers_details',
            'label' => 'section.name.transport_managers.details',
            'route' => 'lva-' . $lva . '/transport_manager_details',
            'pages' => [
                [
                    'id' => $lva . '_transport_managers_details_action',
                    'label' => 'section.name.transport_managers.details.action',
                    'route' => 'lva-' . $lva . '/transport_manager_details/action',
                    'use_route_match' => true
                ]
            ],
            'use_route_match' => true
        ]
    ];
}

$applicationNavigation = array(
    'id' => 'dashboard-applications',
    'label' => 'Applications',
    'route' => 'dashboard',
    'class' => 'proposition-nav__item',
    'pages' => array(
        array(
            'id' => 'application-summary',
            'label' => 'Application summary',
            'route' => 'lva-application/summary',
            'use_route_match' => true
        ),
        array(
            'id' => 'application-submission-summary',
            'label' => 'Application summary',
            'route' => 'lva-application/submission-summary',
            'use_route_match' => true
        ),
        array(
            'id' => 'application',
            'label' => 'Application overview',
            'route' => 'lva-application',
            'use_route_match' => true,
            'pages' => $applicationDetailsPages
        ),
        array(
            'id' => 'licence',
            'label' => 'Licence overview',
            'route' => 'lva-licence',
            'use_route_match' => true,
            'pages' => $licenceDetailsPages
        ),
        array(
            'id' => 'variation-summary',
            'label' => 'Application summary',
            'route' => 'lva-variation/summary',
            'use_route_match' => true
        ),
        array(
            'id' => 'variation-submission-summary',
            'label' => 'Application summary',
            'route' => 'lva-variation/submission-summary',
            'use_route_match' => true
        ),
        array(
            'id' => 'variation',
            'label' => 'Application overview',
            'route' => 'lva-variation',
            'use_route_match' => true,
            'pages' => $variationDetailsPages
        ),
        // Duplicate entry for TM page, corrects the breadcrumb when the user only has access to
        // lva-tm page
        array(
            'id' => 'application_transport_managers_details',
            'label' => 'section.name.transport_managers.details',
            'route' => 'lva-application/transport_manager_details',
            'pages' => [
                [
                    'id' => 'application_transport_managers_details_action',
                    'label' => 'section.name.transport_managers.details.action',
                    'route' => 'lva-application/transport_manager_details/action',
                    'use_route_match' => true
                ]
            ],
            'use_route_match' => true
        ),
        array(
            'id' => 'variation_transport_managers_details',
            'label' => 'section.name.transport_managers.details',
            'route' => 'lva-variation/transport_manager_details',
            'pages' => [
                [
                    'id' => 'variation_transport_managers_details_action',
                    'label' => 'section.name.transport_managers.details.action',
                    'route' => 'lva-variation/transport_manager_details/action',
                    'use_route_match' => true
                ]
            ],
            'use_route_match' => true
        ),
        array(
            'id' => 'dashboard-licences-applications',
            'label' => 'Licences / Applications',
            'route' => 'dashboard',
            'class' => 'proposition-nav__item',
            'pages' => array(
                // dashboard tabs
                array(
                    'id' => 'dashboard-licences',
                    'label' => 'dashboard-nav-licences',
                    'route' => 'dashboard',
                ),
                array(
                    'id' => 'dashboard-fees',
                    'label' => 'dashboard-nav-fees',
                    'route' => 'fees',
                    'pages' => array(
                        array(
                            'id' => 'pay-fees',
                            'label' => 'Pay',
                            'route' => 'fees/pay',
                        ),
                        array(
                            'id' => 'pay-fees-receipt',
                            'label' => 'Pay',
                            'route' => 'fees/receipt',
                        ),
                    ),
                ),
                array(
                    'id' => 'dashboard-correspondence',
                    'label' => 'dashboard-nav-correspondence',
                    'route' => 'correspondence',
                ),
            ),
        ),
    ),
);

$searchNavigation = array(
    'id' => 'search',
    'label' => 'Search',
    'route' => 'search-jump-home',
    'class' => 'proposition-nav__item',
    'pages' => array(
        // --
        array(
            'id' => 'search-operating-centre',
            'label' => 'Find Operating centre',
            'route' => 'search-operating-centre',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-person',
            'label' => 'Find people',
            'route' => 'search-person',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-operator',
            'label' => 'Vehicle Operator details',
            'route' => 'search-operator',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-bus',
            'label' => 'Bus services',
            'route' => 'search-bus',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-traffic-commissioner-publication',
            'label' => 'Traffic Commissioner publications',
            'route' => 'search-traffic-commissioner-publication',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        ),
        array(
            'id' => 'search-vehicle-external',
            'label' => 'Vehicles',
            'route' => 'search-vehicle-external',
            'use_route_match' => true,
            'class' => 'search-navigation__item',
        )
    )
);

return array(
    'router' => array(
        'routes' => array_merge($routes, $configRoutes),
    ),
    'controllers' => array(
        'lva_controllers' => array(
            'LvaApplication'                        => 'Olcs\Controller\Lva\Application\OverviewController',
            'LvaApplication/TypeOfLicence'          => 'Olcs\Controller\Lva\Application\TypeOfLicenceController',
            'LvaApplication/BusinessType'           => 'Olcs\Controller\Lva\Application\BusinessTypeController',
            'LvaApplication/BusinessDetails'        => 'Olcs\Controller\Lva\Application\BusinessDetailsController',
            'LvaApplication/Addresses'              => 'Olcs\Controller\Lva\Application\AddressesController',
            'LvaApplication/People'                 => 'Olcs\Controller\Lva\Application\PeopleController',
            'LvaApplication/OperatingCentres'       => 'Olcs\Controller\Lva\Application\OperatingCentresController',
            'LvaApplication/FinancialEvidence'      => 'Olcs\Controller\Lva\Application\FinancialEvidenceController',
            'LvaApplication/TransportManagers'      => 'Olcs\Controller\Lva\Application\TransportManagersController',
            'LvaApplication/Vehicles'               => 'Olcs\Controller\Lva\Application\VehiclesController',
            'LvaApplication/VehiclesPsv'            => 'Olcs\Controller\Lva\Application\VehiclesPsvController',
            'LvaApplication/Safety'                 => 'Olcs\Controller\Lva\Application\SafetyController',
            'LvaApplication/FinancialHistory'       => 'Olcs\Controller\Lva\Application\FinancialHistoryController',
            'LvaApplication/LicenceHistory'         => 'Olcs\Controller\Lva\Application\LicenceHistoryController',
            'LvaApplication/ConvictionsPenalties'   => 'Olcs\Controller\Lva\Application\ConvictionsPenaltiesController',
            'LvaApplication/Undertakings'           => 'Olcs\Controller\Lva\Application\UndertakingsController',
            'LvaApplication/TaxiPhv'                => 'Olcs\Controller\Lva\Application\TaxiPhvController',
            'LvaApplication/VehiclesDeclarations'   => 'Olcs\Controller\Lva\Application\VehiclesDeclarationsController',
            'LvaApplication/PaymentSubmission'      => 'Olcs\Controller\Lva\Application\PaymentSubmissionController',
            'LvaApplication/Summary'                => 'Olcs\Controller\Lva\Application\SummaryController',
            'LvaApplication/Review'                 => \Common\Controller\Lva\ReviewController::class,
            'LvaLicence'                            => 'Olcs\Controller\Lva\Licence\OverviewController',
            'LvaLicence/Variation'                  => 'Olcs\Controller\Lva\Licence\VariationController',
            'LvaLicence/TypeOfLicence'              => 'Olcs\Controller\Lva\Licence\TypeOfLicenceController',
            'LvaLicence/BusinessType'               => 'Olcs\Controller\Lva\Licence\BusinessTypeController',
            'LvaLicence/BusinessDetails'            => 'Olcs\Controller\Lva\Licence\BusinessDetailsController',
            'LvaLicence/Addresses'                  => 'Olcs\Controller\Lva\Licence\AddressesController',
            'LvaLicence/People'                     => 'Olcs\Controller\Lva\Licence\PeopleController',
            'LvaLicence/OperatingCentres'           => 'Olcs\Controller\Lva\Licence\OperatingCentresController',
            'LvaLicence/TransportManagers'          => 'Olcs\Controller\Lva\Licence\TransportManagersController',
            'LvaLicence/Vehicles'                   => 'Olcs\Controller\Lva\Licence\VehiclesController',
            'LvaLicence/VehiclesPsv'                => 'Olcs\Controller\Lva\Licence\VehiclesPsvController',
            'LvaLicence/Trailers'                   => 'Olcs\Controller\Lva\Licence\TrailersController',
            'LvaLicence/Safety'                     => 'Olcs\Controller\Lva\Licence\SafetyController',
            'LvaLicence/CommunityLicences'          => 'Olcs\Controller\Lva\Licence\CommunityLicencesController',
            'LvaLicence/TaxiPhv'                    => 'Olcs\Controller\Lva\Licence\TaxiPhvController',
            'LvaLicence/Discs'                      => 'Olcs\Controller\Lva\Licence\DiscsController',
            'LvaLicence/ConditionsUndertakings'     => 'Olcs\Controller\Lva\Licence\ConditionsUndertakingsController',
            'LvaVariation'                          => 'Olcs\Controller\Lva\Variation\OverviewController',
            'LvaVariation/TypeOfLicence'            => 'Olcs\Controller\Lva\Variation\TypeOfLicenceController',
            'LvaVariation/BusinessType'             => 'Olcs\Controller\Lva\Variation\BusinessTypeController',
            'LvaVariation/BusinessDetails'          => 'Olcs\Controller\Lva\Variation\BusinessDetailsController',
            'LvaVariation/Addresses'                => 'Olcs\Controller\Lva\Variation\AddressesController',
            'LvaVariation/People'                   => 'Olcs\Controller\Lva\Variation\PeopleController',
            'LvaVariation/OperatingCentres'         => 'Olcs\Controller\Lva\Variation\OperatingCentresController',
            'LvaVariation/TransportManagers'        => 'Olcs\Controller\Lva\Variation\TransportManagersController',
            'LvaVariation/Vehicles'                 => 'Olcs\Controller\Lva\Variation\VehiclesController',
            'LvaVariation/VehiclesPsv'              => 'Olcs\Controller\Lva\Variation\VehiclesPsvController',
            'LvaVariation/Safety'                   => 'Olcs\Controller\Lva\Variation\SafetyController',
            'LvaVariation/CommunityLicences'        => 'Olcs\Controller\Lva\Variation\CommunityLicencesController',
            'LvaVariation/TaxiPhv'                  => 'Olcs\Controller\Lva\Variation\TaxiPhvController',
            'LvaVariation/Discs'                    => 'Olcs\Controller\Lva\Variation\DiscsController',
            'LvaVariation/ConditionsUndertakings'   => 'Olcs\Controller\Lva\Variation\ConditionsUndertakingsController',
            'LvaVariation/Undertakings'             => 'Olcs\Controller\Lva\Variation\UndertakingsController',
            'LvaVariation/FinancialEvidence'        => 'Olcs\Controller\Lva\Variation\FinancialEvidenceController',
            'LvaVariation/VehiclesDeclarations'     => 'Olcs\Controller\Lva\Variation\VehiclesDeclarationsController',
            'LvaVariation/FinancialHistory'         => 'Olcs\Controller\Lva\Variation\FinancialHistoryController',
            'LvaVariation/ConvictionsPenalties'     => 'Olcs\Controller\Lva\Variation\ConvictionsPenaltiesController',
            'LvaVariation/Summary'                  => 'Olcs\Controller\Lva\Variation\SummaryController',
            'LvaVariation/PaymentSubmission'        => 'Olcs\Controller\Lva\Variation\PaymentSubmissionController',
            'LvaVariation/Review'                   => \Common\Controller\Lva\ReviewController::class,
        ),
        'invokables' => array(
            'Olcs\Ebsr\Uploads' => 'Olcs\Controller\Ebsr\UploadsController',
            'Olcs\Ebsr\BusRegistration' => 'Olcs\Controller\Ebsr\BusRegistrationController',
            'Dashboard' => 'Olcs\Controller\DashboardController',
            'Fees' => 'Olcs\Controller\FeesController',
            'Correspondence' => 'Olcs\Controller\CorrespondenceController',
            'User' => 'Olcs\Controller\UserController',
            IndexController::class => IndexController::class,
            SearchController::class => SearchController::class,
            'Search\Result' => 'Olcs\Controller\Search\ResultController',
            'Entity\View' => 'Olcs\Controller\Entity\ViewController',
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'service_manager' => array(
        'aliases' => [
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service',
        ],
        'invokables' => array(
            'ApplicationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter',
            'LicencePeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicencePeopleAdapter',
            'VariationPeopleAdapter'
                => 'Olcs\Controller\Lva\Adapters\VariationPeopleAdapter',
            'LicenceTransportManagerAdapter'
                => 'Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter',
            'DashboardProcessingService'
                => 'Olcs\Service\Processing\DashboardProcessingService',
            'Email\TransportManagerCompleteDigitalForm'
                => 'Olcs\Service\Email\TransportManagerCompleteDigitalForm',
        ),
        'factories' => array(
            'Olcs\InputFilter\EbsrPackInput' => 'Olcs\InputFilter\EbsrPackFactory',
            'Olcs\Service\Ebsr' => 'Olcs\Service\Ebsr',
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Olcs\Navigation\DashboardNavigation' => 'Olcs\Navigation\DashboardNavigationFactory',
        )
    ),
    'search' => [
        'invokables' => [
            'operator'    => LicenceSelfserve::class, // Selfserve licence search
            'vehicle'     => \Common\Data\Object\Search\Vehicle::class,
            'vehicle-external' => \Common\Data\Object\Search\VehicleSelfServe::class,
            'bus'         => \Common\Data\Object\Search\BusRegSelfServe::class,
            'person'      => PeopleSelfserveSearchIndex::class,
            'operating-centre' => OperatingCentreSearchIndex::class,
            'traffic-commissioner-publication' => \Common\Data\Object\Search\TrafficCommissionerPublications::class,
        ]
    ],
    'form_elements' => [
        'factories' => [
            SearchFilterFieldset::class => SearchFilterFieldsetFactory::class,
            SearchDateRangeFieldset::class => SearchDateRangeFieldsetFactory::class
        ],
        'aliases' => [
            'SearchFilterFieldset' => SearchFilterFieldset::class,
            'SearchDateRangeFieldset' => SearchDateRangeFieldset::class
        ]
    ],
    'controller_plugins' => array(
        'invokables' => array()
    ),
    'simple_date_format' => array(
        'default' => 'd-m-Y'
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layouts/base.phtml',
            'layout/ajax' => __DIR__ . '/../view/layouts/ajax.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../../../vendor/olcs/OlcsCommon/Common/view',
            __DIR__ . '/../view'
        )
    ),
    'navigation' => array(
        'default' => array(
            array(
                'id' => 'home',
                'label' => 'Home',
                'route' => 'index',
                'pages' => array(

                    $searchNavigation,

                    array(
                        'id' => 'selfserve-topnav-bus-registration',
                        'label' => 'Bus registrations',
                        'route' => 'bus-registration',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ),

                    $applicationNavigation,

                    /*array(
                        'id' => 'my-account',
                        'label' => 'My Account',
                        'route' => 'user',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'manage-users',
                        'label' => 'Manage Users',
                        'route' => 'user',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'bus-services',
                        'label' => 'Bus Services',
                        'route' => 'user',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    ),
                    array(
                        'id' => 'bus-services',
                        'label' => 'MBus Services',
                        'route' => 'user',
                        'use_route_match' => true,
                        'class' => 'proposition-nav__item',
                    )*/
                ),
            ),
        )
    ),
    'asset_path' => '//dev_dvsa-static.web01.olcs.mgt.mtpdvsa',
    'service_api_mapping' => array(
        'endpoints' => array(
            'ebsr' => 'http://olcs-ebsr/'
        )
    ),
    'rest_services' => array(
        'delegators' => [
            'Olcs\RestService\ebsr\pack' => ['Olcs\Service\Rest\EbsrPackDelegatorFactory']
        ]
    ),
    'form_service_manager' => [
        'invokables' => [
            // Operating Centres
            'lva-licence-operating_centres' => 'Olcs\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres',
            'lva-variation-operating_centres' => 'Olcs\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres',
            'lva-application-operating_centres'
                => 'Olcs\FormService\Form\Lva\OperatingCentres\ApplicationOperatingCentres',
            // Business Type
            'lva-application-business_type' => 'Olcs\FormService\Form\Lva\BusinessType\ApplicationBusinessType',
            'lva-licence-business_type' => 'Olcs\FormService\Form\Lva\BusinessType\LicenceBusinessType',
            'lva-variation-business_type' => 'Olcs\FormService\Form\Lva\BusinessType\VariationBusinessType',
            //
            'lva-lock-business_details' => 'Olcs\FormService\Form\Lva\LockBusinessDetails',
            'lva-licence-business_details' => 'Olcs\FormService\Form\Lva\LicenceBusinessDetails',
            'lva-variation-business_details' => 'Olcs\FormService\Form\Lva\VariationBusinessDetails',
            'lva-application-business_details' => 'Olcs\FormService\Form\Lva\ApplicationBusinessDetails',
            // Goods vehicle filter form service
            'lva-application-goods-vehicles-filters' => 'Olcs\FormService\Form\Lva\ApplicationGoodsVehiclesFilters',
            // External common goods vehicles vehicle form service
            'lva-application-goods-vehicles-add-vehicle' => \Olcs\FormService\Form\Lva\GoodsVehicles\AddVehicle::class,
            'lva-licence-goods-vehicles-add-vehicle' => \Olcs\FormService\Form\Lva\GoodsVehicles\AddVehicle::class,
            'lva-variation-goods-vehicles-add-vehicle' => \Olcs\FormService\Form\Lva\GoodsVehicles\AddVehicle::class,
            'lva-application-goods-vehicles-edit-vehicle'
                => \Olcs\FormService\Form\Lva\GoodsVehicles\EditVehicle::class,
            'lva-licence-goods-vehicles-edit-vehicle' => \Olcs\FormService\Form\Lva\GoodsVehicles\EditVehicle::class,
            'lva-variation-goods-vehicles-edit-vehicle' => \Olcs\FormService\Form\Lva\GoodsVehicles\EditVehicle::class,
            // External common psv vehicles vehicle form service
            'lva-psv-vehicles-vehicle' => 'Olcs\FormService\Form\Lva\PsvVehiclesVehicle',
            // External common vehicles vehicle form service (Goods and PSV)
            'lva-vehicles-vehicle' => 'Olcs\FormService\Form\Lva\VehiclesVehicle'
        ],
    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\RoutePermissionsGuard' => [

                // Search and who can access them
                'search-operating-centre' => [
                    'partner-user',
                    'partner-admin'
                ],
                'search-person' => [
                    'partner-user',
                    'partner-admin'
                ],
                'search-vehicle-external' => [
                    'partner-user',
                    'partner-admin'
                ],

                // Bus reg stuff and who can access
                'ebsr' => ['selfserve-ebsr'],
                'bus-registration' => [
                    'selfserve-landing-page-bus-registration'
                ],

                // Selfserve search
                'search-operating-centre' => ['selfserve-search-operating-centre'],
                'search-person' => ['selfserve-search-person'],
                'search-vehicle-external' => ['selfserve-search-vehicle-external'],

                'lva-application/transport_manager_details*' => ['selfserve-tm'],
                'lva-variation/transport_manager_details*' => ['selfserve-tm'],
                'lva-*' => ['selfserve-lva'],
                'manage-user' => ['selfserve-manage-user'], // route -> permission
                'user' => ['selfserve-manage-user'],
                'zfcuser/login' => ['*'],
                'zfcuser/logout' => ['*'],
                'search*' => ['*'],
                'index' => ['*'],
                '*' => ['selfserve-user'],
            ]
        ]
    ],
    'business_rule_manager' => [
        'invokables' => [
            'UserMappingContactDetails' => 'Olcs\BusinessRule\Rule\UserMappingContactDetails',
        ]
    ],
    'business_service_manager' => [
        'invokables' => [
            'Lva\LicenceAddresses' => 'Olcs\BusinessService\Service\Lva\LicenceVariationAddresses',
            'Lva\VariationAddresses' => 'Olcs\BusinessService\Service\Lva\LicenceVariationAddresses',
            'Lva\AddressesChangeTask' => 'Olcs\BusinessService\Service\Lva\AddressesChangeTask',
        ]
    ]
);
