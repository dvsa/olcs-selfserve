<?php

namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\LicenceSoleTrader as CommonLicenceSoleTrader;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Lva\PeopleLvaService;
use ZfcRbac\Service\AuthorizationService;

/**
 * Licence Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceSoleTrader extends CommonLicenceSoleTrader
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected PeopleLvaService $peopleLvaService;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        PeopleLvaService $peopleLvaService,
        FormServiceManager $formServiceLocator
    ) {
        parent::__construct($formHelper, $authService, $peopleLvaService, $formServiceLocator);
    }
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return Form
     */
    public function alterForm($form, array $params)
    {
        $form = parent::alterForm($form, $params);

        $this->removeStandardFormActions($form);

        return $form;
    }
}
