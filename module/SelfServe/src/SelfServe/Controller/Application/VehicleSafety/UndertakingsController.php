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
     * Load the data for the form and format it in a way that the fieldsets can
     * understand.
     *
     * @param arary $data
     * @return array
     */
    protected function processLoad($data)
    {
        $translate = $this->getServiceLocator()->get('viewhelpermanager')->get('translate');

        $data['application'] = array(
            'id' => $data['id'],
            'version' => $data['version'],
            'status' => $data['status']
        );

        // Load up the data in a format which can be understood by the fieldsets
        $data['smallVehiclesIntention'] = array(
            'psvOperateSmallVehicles' => (isset($data['psvOperateSmallVehicles'])?
                                                    $data['psvOperateSmallVehicles']:false),
            'psvSmallVehicleNotes' => (isset($data['psvSmallVehicleNotes'])?
                                                    $data['psvSmallVehicleNotes']:""),
            'psvSmallVehicleUndertakings' =>
                $translate('application_vehicle-safety_undertakings.smallVehiclesUndertakings.text'),
            'psvSmallVehicleScotland' =>
                $translate('application_vehicle-safety_undertakings.smallVehiclesUndertakingsScotland.text'),
            'psvSmallVehicleConfirmation' => (isset($data['psvSmallVehicleConfirmation'])?
                                                    $data['psvSmallVehicleConfirmation']:false)
        );

        $data['nineOrMore'] = array(
            'psvNoSmallVehiclesConfirmation' => (isset($data['psvNoSmallVehiclesConfirmation'])?
                                                $data['psvNoSmallVehiclesConfirmation']:false)
        );

        $data['limousinesNoveltyVehicles'] = array(
            'psvLimousines' => (isset($data['psvLimousines'])?
                                                    $data['psvLimousines']:false),
            'psvNoLimousineConfirmation' => (isset($data['psvNoLimousineConfirmation'])?
                                                    $data['psvNoLimousineConfirmation']:false),
            'psvOnlyLimousinesConfirmation' => (isset($data['psvOnlyLimousinesConfirmation'])?
                                                    $data['psvOnlyLimousinesConfirmation']:false)
        );

        return $data;
    }

    /**
     * Add customisation to the form dependent on which of five scenarios
     * is in play for OLCS-2855
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
        $arrayCheck=array('totAuthSmallVehicles','totAuthMediumVehicles','totAuthLargeVehicles');
        foreach ($arrayCheck as $attribute) {
            if ( is_null($data[$attribute]) ) {
                $data[$attribute]=0;
            }
        }

        // Now remove the form fields we don't need to display to the user.
        if ( $data['totAuthSmallVehicles'] == 0 ) {
            // no smalls - case 3
            $form->remove('smallVehiclesIntention');
        } else {
            // Small vehicles - cases 1, 2, 4, 5
            if ( ( $data['totAuthMediumVehicles'] == 0 )
                    && ( $data['totAuthLargeVehicles'] == 0 ) ) {
                // Small only, cases 1, 2
                if ( $data['trafficArea']['applyScottishRules'] ) {
                    // Case 2 - Scottish small only
                    $form->get('smallVehiclesIntention')->remove('psvOperateSmallVehicles');
                    $form->get('smallVehiclesIntention')->remove('psvSmallVehicleNotes');
                    $form->remove('nineOrMore');
                    $form->get('limousinesNoveltyVehicles')->remove('psvOnlyLimousinesConfirmationLabel');
                    $form->get('limousinesNoveltyVehicles')->remove('psvOnlyLimousinesConfirmation');
                } else {
                    // Case 1 - England/Wales small only
                    $form->remove('nineOrMore');
                    $form->get('limousinesNoveltyVehicles')->remove('psvOnlyLimousinesConfirmationLabel');
                    $form->get('limousinesNoveltyVehicles')->remove('psvOnlyLimousinesConfirmation');
                }
            } else {
                // cases 4, 5
                if ( $data['trafficArea']['applyScottishRules'] ) {
                    // Case 5 Mix Scotland
                    $form->get('smallVehiclesIntention')->remove('psvOperateSmallVehicles');
                    $form->get('smallVehiclesIntention')->remove('psvSmallVehicleNotes');
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
