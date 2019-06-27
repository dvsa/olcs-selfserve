<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * @todo clearly this will need to be a lot better later - but will wait to see first if it's staying
 *
 * Fee list mapper
 */
class FeeList
{
    /**
     * @param array $data
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        $currency = new CurrencyFormatter();

        $irhpPermitStock = $data['application']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

        $totalPermitsRequired = $data['application']['totalPermitsRequired'];
        $applicationFeePerPermit = $data['irhpFeeList']['fee']['IRHP_GV_APP_ECMT']['fixedValue'];
        $permitsRequiredLines = EcmtNoOfPermits::mapForDisplay($data['application'], $translator, $url);

        $data['application']['summaryData'] = [
            [
                'key' => 'permits.page.fee.application.reference',
                'value' => $data['application']['applicationRef']
            ],
            [
                'key' => 'permits.page.fee.application.date',
                'value' => date(\DATE_FORMAT, strtotime($data['application']['dateReceived']))
            ],
            [
                'key' => 'permits.page.fee.permit.type',
                'value' => $data['application']['permitType']['description']
            ],
            [
                'key' => 'permits.page.fee.permit.year',
                'value' => date('Y', strtotime($irhpPermitStock['validTo']))
            ],
            [
                'key' => 'permits.page.fee.number.permits',
                'value' => implode('<br/>', $permitsRequiredLines),
                'disableHtmlEscape' => true
            ],
            [
                'key' => 'permits.page.fee.application.fee.per.permit',
                'value' => $applicationFeePerPermit,
                'isCurrency' => true
            ],
            [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.fee.non-refundable',
                    [$currency($applicationFeePerPermit * $totalPermitsRequired)]
                )
            ]
        ];

        return $data['application'];
    }
}
