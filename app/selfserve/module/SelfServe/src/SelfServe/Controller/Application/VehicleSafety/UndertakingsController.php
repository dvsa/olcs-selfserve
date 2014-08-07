<?php

/**
 * Vehicle Undertakings Controller (OLCS-2855)
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Controller\Application\VehicleSafety;

/**
 * Vehicle Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class UndertakingsController extends VehicleSafetyController
{

    /**
     * Action data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'application',
                'smallVehiclesIntention',
                'smallVehiclesUndertakings',
                'nineOrMore',
                'limousinesNoveltyVehicles'
            )
        )
    );

    /**
     * Holds the licenceDataBundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'status',
            'totAuthSmallVehicles',
            'totAuthMediumVehicles',
            'totAuthLargeVehicles',
            'psvOperateSmallVehicles',
            'psvSmallVehicleNotes',
            'psvSmallVehicleConfirmation',
            'psvNoSmallVehicleConfirmation',
            'psvLimousines',
            'psvNoLimousineConfirmation',
            'psvOnlyLimousineConfirmation',
        ),
        'children' => array(
            'trafficArea' => array(
                'properties' => array(
                    'id',
                    'applyScottishRules',
                ),
            )
        )
    );

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Add customisation to the table
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $data = $this->load($this->getIdentifier());
        
        // If this traffic area has no Scottish Rules flag, set it to false.
        if ( !isset($data['trafficArea']['applyScottishRules']) ) {
            $data['trafficArea']['applyScottishRules']=false;
        }

        // In some cases, totAuthSmallVehicles etc. can be set NULL, and we
        // need to evaluate as zero, so fix that here.
        if ( is_null($data['totAuthSmallVehicles']) ) {
            $data['totAuthSmallVehicles']=0;
        }

        if ( is_null($data['totAuthMediumVehicles']) ) {
            $data['totAuthMediumVehicles']=0;
        }

        if ( is_null($data['totAuthLargeVehicles']) ) {
            $data['totAuthLargeVehicles']=0;
        }

        // Now remove the form fields we don't need to display to the user.
        if ( $data['totAuthSmallVehicles'] == 0 ) {
            // no smalls - case 3
            $form->remove('smallVehiclesIntention');
            $form->remove('smallVehiclesUndertakings');
        } else {
            // Small vehicles - cases 1, 2, 4, 5
            if ( ( $data['totAuthMediumVehicles'] == 0 )
                    && ( $data['totAuthLargeVehicles'] == 0 ) ) {
                // Small only, cases 1, 2
                if ( $data['trafficArea']['applyScottishRules'] ) {
                    // Case 2 - Scottish small only
                    $form->remove('smallVehiclesIntention');
                    $form->remove('nineOrMore');
                    $form->get('limousinesNoveltyVehicles')->remove('psvOnlyLimousinesConfirmation');
                } else {
                    // Case 1 - England/Wales small only
                    $form->remove('nineOrMore');
                    $form->get('limousinesNoveltyVehicles')->remove('psvOnlyLimousinesConfirmation');
                }
            } else {
                // cases 4, 5
                if ( $data['trafficArea']['applyScottishRules'] ) {
                    // Case 5 Mix Scotland
                    $form->remove('smallVehiclesIntention');
                    $form->remove('nineOrMore');
                } else {
                    // Case 4 Mix England/Wales
                    $form->remove('nineOrMore');
                }

            }
        }

        return $form;
    }
}