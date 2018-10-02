<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

/**
 * Generate the ECMT Licence Page title
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */
class EcmtLicenceData extends AbstractHelper
{

    /**
     * Determines which title to display for the Ecmt Licence page.
     *
     * If there are applications against all the organisation's licences then
     * the $licences should be empty and a warning/error message is displayed
     * instead of the question title.
     *
     * @param object $form
     * @param array $application
     * @return string
     */
    public function __invoke($form, $application = [], $stock)
    {
        $permitType = isset($application['permitType']['description']) ? $application['permitType']['description'] : 'ECMT';
        //$validFrom = date('d F Y', strtotime($stock[0]['validFrom']));
        //$validTo = date('d F Y', strtotime($stock[0]['validTo']));
        $validFrom = '01 January 2019';
        $validTo = '31 December 2019';
        $licences = $form->get('Fields')->get('EcmtLicence')->getValueOptions();
        $licenceCount = 0;
        foreach ($licences as $licence) {
            if ($licence['value'] !== '') {
                $licenceCount++;
            }
        }

        $data['empty'] = false;

        if ($licenceCount === 0) {
            $data['title'] = $this->view->translate('permits.page.ecmt.licence.saturated');
            $data['copy'] = sprintf($this->view->translate('markup-ecmt-licence-saturated'), '/permits');
            $data['empty'] = true;
            if (!empty($application)) {
                $data['title'] = $this->formatTitle(
                    $application['licence']['licNo'],
                    $application['licence']['licenceType']['id'],
                    $application['licence']['trafficArea']['name']
                );
                $data['copy'] = '<p class="guidance-blue extra-space large">' .
                    sprintf(
                        $this->view->translate('permits.page.ecmt.licence.info'),
                        $permitType,
                        $validFrom,
                        $validTo
                    ) . '</p>';
            }
            return $data;
        }

        $data['title'] = $this->view->translate('permits.page.ecmt.licence.question');
        $data['copy'] = '<p class="guidance-blue extra-space large">' .
            sprintf(
                $this->view->translate('permits.page.ecmt.licence.info'),
                $permitType,
                $validFrom,
                $validTo
            ) . '</p>';

        if ($licenceCount === 1) {
            $data['title'] = sprintf(
                $this->view->translate('permits.page.ecmt.licence.question.one.licence'),
                preg_replace("/<div(.*?)>(.*?)<\/div>/i", "", $licences[0]['label'])
            );
        }

        if (empty($application) && $licenceCount === 1 && array_key_exists('html_elements', $licences[0])) {
            $data['copy'] .= '<p>' . $this->view->translate('permits.form.ecmt-licence.restricted-licence.hint') . '</p>';
        }

        if (!empty($application)) {
            $data['title'] = $this->formatTitle(
                $application['licence']['licNo'],
                $application['licence']['licenceType']['id'],
                $application['licence']['trafficArea']['name']
            );
        }
        return $data;
    }

    private function formatTitle($licNo, $licType, $trafficArea) {
        $title = sprintf(
            $this->view->translate('permits.page.ecmt.licence.question.one.licence'),
            $licNo . ' ' .
            $this->view->translate($licType) .
            ' (' . $this->view->translate($trafficArea) . ')'
        );

        return $title;
    }
}
