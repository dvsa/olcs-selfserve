<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentres;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use Common\Service\Helper\FormHelperService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use ZfcRbac\Service\AuthorizationService;

/**
 * Application Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentres extends AbstractOperatingCentres
{
    use ButtonsAlterations;

    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected $tableBuilder;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        $tableBuilder,
        FormServiceManager $formServiceLocator
    ) {
        $this->formHelper = $formHelper;
        $this->authService = $authService;
        $this->tableBuilder = $tableBuilder;
        $this->formServiceLocator = $formServiceLocator;
    }

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params paramas
     *
     * @return void
     */
    protected function alterForm(Form $form, array $params)
    {
        $this->formServiceLocator->get('lva-application')->alterForm($form);

        parent::alterForm($form, $params);
        $this->alterButtons($form);

        if ($form->has('table')) {
            $table = $form->get('table')->get('table')->getTable();
            $table->removeColumn('noOfComplaints');
        }

        if ($form->get('data')->has('totCommunityLicencesFieldset')) {
            $this->formHelper->alterElementLabel(
                $form->get('data')->get('totCommunityLicencesFieldset')->get('totCommunityLicences'),
                '-external-app',
                FormHelperService::ALTER_LABEL_APPEND
            );
        }

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }
    }
}
