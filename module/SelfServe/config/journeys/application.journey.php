<?php

/**
 * Application journey config
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
return array(
    'Application' => array(
        'homeRoute' => 'home/dashboard',
        'identifier' => 'applicationId',
        'completionService' => 'ApplicationCompletion',
        'completionStatusJourneyIdColumn' => 'application',
        'completionStatusMap' => array(
            0 => '',
            1 => 'incomplete',
            2 => 'complete'
        ),
        'sections' => array(
            'TypeOfLicence' => array(
                'restriction' => array(
                    null,
                    'goods',
                    'psv',
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(
                    'OperatorLocation' => array(
                    ),
                    'OperatorType' => array(
                        'restriction' => array(
                            'gb'
                        ),
                        'required' => array(
                            'TypeOfLicence/OperatorLocation'
                        )
                    ),
                    'LicenceType' => array(
                        'required' => array(
                            'TypeOfLicence/OperatorType'
                        )
                    )
                )
            ),
            'YourBusiness' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(
                    'BusinessType' => array(

                    ),
                    'BusinessDetails' => array(
                        'required' => array(
                            'YourBusiness/BusinessType'
                        )
                    ),
                    'Addresses' => array(
                        'required' => array(
                            'YourBusiness/BusinessType'
                        )

                    ),
                    'People' => array(
                        'required' => array(
                            'YourBusiness/BusinessType'
                        )
                    )
                )
            ),
            'TaxiPhv' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'psv-special-restricted'
                ),
                'subSections' => array(
                    'Placeholder' => array(

                    )
                )
            ),
            'OperatingCentres' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted'
                ),
                'subSections' => array(
                    'Authorisation' => array(

                    ),
                    'FinancialEvidence' => array(

                    )
                )
            ),
            'TransportManagers' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'goods-standard',
                    'psv-standard'
                ),
                'subSections' => array(
                    'Placeholder' => array(

                    )
                )
            ),
            'VehicleSafety' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted'
                ),
                'subSections' => array(
                    'Vehicle' => array(

                    ),
                    'Safety' => array(

                    )
                )
            ),
            'PreviousHistory' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted'
                ),
                'subSections' => array(
                    'FinancialHistory' => array(

                    ),
                    'LicenceHistory' => array(

                    ),
                    'ConvictionsPenalties' => array(

                    )
                )
            ),
            'ReviewDeclarations' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(
                    'Summary' => array(

                    )
                )
            ),
            'PaymentSubmission' => array(
                'required' => array(
                    'TypeOfLicence'
                ),
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(
                    'Payment' => array(
                        'restriction' => array(
                            'unpaid'
                        )
                    ),
                    'Summary' => array(
                        'restriction' => array(
                            'paid'
                        )
                    )
                )
            )
        )
    )
);
