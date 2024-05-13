<?php

use Dvsa\Olcs\Application\Controller as ApplicationControllers;
use Dvsa\Olcs\Application\Controller\Factory as ApplicationControllerFactories;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'plugins' => [
        'aliases' => [
            'Application' => ApplicationControllers\OverviewController::class,
            'Application/TypeOfLicence' => ApplicationControllers\TypeOfLicenceController::class,
            'Application/BusinessType' => ApplicationControllers\BusinessTypeController::class,
            'Application/BusinessDetails' => ApplicationControllers\BusinessDetailsController::class,
            'Application/Addresses' => ApplicationControllers\AddressesController::class,
            'Application/People' => ApplicationControllers\PeopleController::class,
            'Application/OperatingCentres' => ApplicationControllers\OperatingCentresController::class,
            'Application/FinancialEvidence' => ApplicationControllers\FinancialEvidenceController::class,
            'Application/VehiclesPsv' => ApplicationControllers\VehiclesPsvController::class,
            'Application/Safety' => ApplicationControllers\SafetyController::class,
            'Application/FinancialHistory' => ApplicationControllers\FinancialHistoryController::class,
            'Application/LicenceHistory' => ApplicationControllers\LicenceHistoryController::class,
            'Application/ConvictionsPenalties' => ApplicationControllers\ConvictionsPenaltiesController::class,
            'Application/Undertakings' => ApplicationControllers\UndertakingsController::class,
            'Application/TaxiPhv' => ApplicationControllers\TaxiPhvController::class,
            'Application/VehiclesDeclarations' => ApplicationControllers\VehiclesDeclarationsController::class,
            'Application/PaymentSubmission' => ApplicationControllers\PaymentSubmissionController::class,
            'Application/Summary' => ApplicationControllers\SummaryController::class,
            'Application/UploadEvidence' => ApplicationControllers\UploadEvidenceController::class,
            'Application/Review' => \Common\Controller\Lva\ReviewController::class,
            'Application/Vehicles' => ApplicationControllers\AddVehiclesQuestionController::class,
            'Application/TransportManagers' => ApplicationControllers\TransportManagersController::class,
        ],
        'factories' => [
            ApplicationControllers\AddVehiclesQuestionController::class => ApplicationControllerFactories\AddVehiclesQuestionControllerFactory::class,
            ApplicationControllers\AddressesController::class => ApplicationControllerFactories\AddressesControllerFactory::class,
            ApplicationControllers\BusinessDetailsController::class => ApplicationControllerFactories\BusinessDetailsControllerFactory::class,
            ApplicationControllers\BusinessTypeController::class => ApplicationControllerFactories\BusinessTypeControllerFactory::class,
            ApplicationControllers\ConvictionsPenaltiesController::class => ApplicationControllerFactories\ConvictionsPenaltiesControllerFactory::class,
            ApplicationControllers\FinancialEvidenceController::class => ApplicationControllerFactories\FinancialEvidenceControllerFactory::class,
            ApplicationControllers\FinancialHistoryController::class => ApplicationControllerFactories\FinancialHistoryControllerFactory::class,
            ApplicationControllers\LicenceHistoryController::class => ApplicationControllerFactories\LicenceHistoryControllerFactory::class,
            ApplicationControllers\OperatingCentresController::class => ApplicationControllerFactories\OperatingCentresControllerFactory::class,
            ApplicationControllers\OverviewController::class => ApplicationControllerFactories\OverviewControllerFactory::class,
            ApplicationControllers\PaymentSubmissionController::class => ApplicationControllerFactories\PaymentSubmissionControllerFactory::class,
            ApplicationControllers\PeopleController::class => ApplicationControllerFactories\PeopleControllerFactory::class,
            ApplicationControllers\SafetyController::class => ApplicationControllerFactories\SafetyControllerFactory::class,
            ApplicationControllers\SummaryController::class => ApplicationControllerFactories\SummaryControllerFactory::class,
            ApplicationControllers\TaxiPhvController::class => ApplicationControllerFactories\TaxiPhvControllerFactory::class,
            ApplicationControllers\TypeOfLicenceController::class => ApplicationControllerFactories\TypeOfLicenceControllerFactory::class,
            ApplicationControllers\UndertakingsController::class => ApplicationControllerFactories\UndertakingsControllerFactory::class,
            ApplicationControllers\UploadEvidenceController::class => ApplicationControllerFactories\UploadEvidenceControllerFactory::class,
            ApplicationControllers\VehiclesDeclarationsController::class => ApplicationControllerFactories\VehiclesDeclarationsControllerFactory::class,
            ApplicationControllers\TransportManagersController::class => ApplicationControllerFactories\TransportManagersControllerFactory::class,
            ApplicationControllers\VehiclesPsvController::class => ApplicationControllerFactories\VehiclesPsvControllerFactory::class,
            ApplicationControllers\GovUkOneLoginRedirectController::class => InvokableFactory::class
        ],
    ],
];
