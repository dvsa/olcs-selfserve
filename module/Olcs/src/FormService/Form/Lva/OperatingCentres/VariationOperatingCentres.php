<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres as CommonVariationOperatingCentres;
use Common\Service\Table\TableBuilder;
use Laminas\Form\Form;

/**
 * Variation Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentres extends CommonVariationOperatingCentres
{
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        if ($form->has('table')) {
            $table = $form->get('table')->get('table')->getTable();
            $table->removeColumn('noOfComplaints');
        }

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }
        $this->getFormHelper()->remove($form, 'form-actions->cancel');
    }

    protected function alterFormForPsvLicences(Form $form, array $params)
    {
        parent::alterFormForPsvLicences($form, $params);
        $this->alterFormWithTranslationKey($form, 'community-licence-changes-contact-office.psv');
    }

    /**
     * @inheritDoc
     */
    protected function alterFormForGoodsLicences(Form $form, array $params): void
    {
        parent::alterFormForGoodsLicences($form, $params);
        $this->alterFormWithTranslationKey($form, 'community-licence-changes-contact-office');
    }

    /**
     * Apply a padlock to the totCommunityLicences field using the specified translation key as a tooltip
     *
     * @param Form $form
     * @param string $translationKey
     *
     * @return void
     */
    protected function alterFormWithTranslationKey(Form $form, $translationKey)
    {
        if ($form->get('data')->has('totCommunityLicencesFieldset')) {
            $this->getFormHelper()->lockElement(
                $form->get('data')->get('totCommunityLicencesFieldset')->get('totCommunityLicences'),
                $translationKey
            );
        }
    }
}
