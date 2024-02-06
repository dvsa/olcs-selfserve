<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

class MessagingAppOrLicNo extends AbstractListDataService
{
    /**
     * Fetch list data
     *
     * @param array $context Parameters
     *
     * @return array
     * @throw DataServiceException
     */
    public function fetchListData($context = null)
    {
        $data = (array)$this->getData('licapp');

        if (0 !== count($data)) {
            return $data;
        }

        $response = $this->handleQuery(
            TransferQry\Messaging\ApplicationLicenceList\ByOrganisation::create([])
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();

        $this->setData('licapp', (isset($result['results']) ? $result['results'] : null));

        return $this->getData('licapp');
    }

    public function formatDataForGroups(array $data)
    {
        $optionData = [
            [
                'label' => 'Licence',
                'options' => []
            ],
            [
                'label' => 'Application',
                'options' => []
            ]
        ];

        $optionData[0]['options'] = $data['licences'];
        $optionData[1]['options'] = $data['applications'];

        $this->prefixArrayKey($optionData[0]['options'], 'L');
        $this->prefixArrayKey($optionData[1]['options'], 'A');

        return $optionData;
    }

    private function prefixArrayKey(array &$array, string $prefix): void
    {
        foreach ($array as $k => $v)
        {
            $array[$prefix . $k] = $v;
            unset($array[$k]);
        }
    }
}
