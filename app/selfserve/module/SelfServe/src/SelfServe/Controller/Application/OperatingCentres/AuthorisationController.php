<?php

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Application\OperatingCentres;

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AuthorisationController extends OperatingCentresController
{
    /**
     * Holds the table data
     *
     * @var array
     */
    private $tableData = null;

    /**
     * Holds the data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'totAuthSmallVehicles',
            'totAuthMediumVehicles',
            'totAuthLargeVehicles',
            'totCommunityLicences',
            'totAuthVehicles',
            'totAuthTrailers',
        ),
        'children' => array(
            'licence' => array(
                'properties' => array(
                    'id'
                ),
                'children' => array(
                    'trafficArea' => array(
                        'properties' => array(
                            'id',
                            'name'
                        )
                    )
                )
            )
        )
    );

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data',
                'dataTrafficArea'
            ),
        ),
    );

    /**
     * Holds the sub action service
     *
     * @var string
     */
    protected $actionService = 'ApplicationOperatingCentre';

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        '_addresses' => array(
            'address'
        ),
        'main' => array(
            'children' => array(
                'applicationOperatingCentre' => array(
                    'mapFrom' => array(
                        'data',
                        'advertisements'
                    )
                ),
                'operatingCentre' => array(
                    'mapFrom' => array(
                        'operatingCentre'
                    ),
                    'children' => array(
                        'addresses' => array(
                            'mapFrom' => array(
                                'addresses'
                            )
                        )
                    )
                ),
            )
        )
    );

    /**
     * Holds the actionDataBundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'numberOfTrailers',
            'numberOfVehicles',
            'sufficientParking',
            'permission',
            'adPlaced',
            'adPlacedIn',
            'dateAdPlaced'
        ),
        'children' => array(
            'operatingCentre' => array(
                'properties' => array(
                    'id',
                    'version'
                ),
                'children' => array(
                    'address' => array(
                        'properties' => array(
                            'id',
                            'version',
                            'addressLine1',
                            'addressLine2',
                            'addressLine3',
                            'addressLine4',
                            'postcode',
                            'county',
                            'city',
                            'country'
                        )
                    ),
                    'adDocuments' => array(
                        'properties' => array(
                            'id',
                            'version',
                            'fileName',
                            'identifier',
                            'size'
                        )
                    )
                )
            ),
            'application' => array(
                'properties' => null,
                'children' => array(
                    'licence' => array(
                        'properties' => null,
                        'children' => array(
                            'trafficArea' => array(
                                'properties' => array(
                                    'id'
                                )
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Holds the Traffic Area details
     *
     * @var array
     */
    private $trafficArea;

    /**
     * Northern Ireland Traffic Area Code
     */
    const NORTHERN_IRELAND_TRAFFIC_AREA_CODE = 'N';

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Add operating centre
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit operating centre
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete sub action
     *
     * @return Response
     */
    public function deleteAction()
    {
        $this->maybeClearTrafficAreaId();
        return $this->delete();
    }

    /**
     * Remove trailer related columns for PSV
     *
     * @param object $table
     * @return object
     */
    protected function alterTable($table)
    {
        if ($this->isPsv()) {
            $cols = $table->getColumns();

            unset($cols['trailersCol']);

            $table->setColumns($cols);

            $footer = $table->getFooter();

            $footer['total']['content'] .= '-psv';

            unset($footer['trailersCol']);

            $table->setFooter($footer);
        }

        return $table;
    }

    /**
     * Remove trailer elements for PSV and set up Traffic Area section
     *
     * @param object $form
     * @return object
     */
    protected function alterForm($form)
    {
        if ($this->isPsv()) {

            $options = $form->get('data')->getOptions();
            $options['hint'] .= '.psv';
            $form->get('data')->setOptions($options);

            if (!in_array($this->getLicenceType(), array('standard-national', 'standard-international'))) {
                $form->get('data')->remove('totAuthLargeVehicles');
            }

            if (!in_array($this->getLicenceType(), array('standard-international', 'restricted'))) {
                $form->get('data')->remove('totCommunityLicences');
            }

            $form->get('data')->remove('totAuthVehicles');
            $form->get('data')->remove('totAuthTrailers');
            $form->get('data')->remove('minTrailerAuth');
            $form->get('data')->remove('maxTrailerAuth');

        } else {

            $form->get('data')->remove('totAuthSmallVehicles');
            $form->get('data')->remove('totAuthMediumVehicles');
            $form->get('data')->remove('totAuthLargeVehicles');
            $form->get('data')->remove('totCommunityLicences');
        }

        // set up Traffic Area section
        $operatingCentresExists = count($this->tableData);
        $trafficArea = $this->getTrafficArea();
        $trafficAreaId = $trafficArea ? $trafficArea['id'] : '';
        if (!$operatingCentresExists) {
            $form->remove('dataTrafficArea');
        } elseif ($trafficAreaId) {
            $form->get('dataTrafficArea')->remove('trafficArea');
            $template = $form->get('dataTrafficArea')->get('trafficAreaInfoNameExists')->getValue();
            $newValue = str_replace('%NAME%', $trafficArea['name'], $template);
            $form->get('dataTrafficArea')->get('trafficAreaInfoNameExists')->setValue($newValue);
        } else {
            $form->get('dataTrafficArea')->remove('trafficAreaInfoLabelExists');
            $form->get('dataTrafficArea')->remove('trafficAreaInfoNameExists');
            $form->get('dataTrafficArea')->remove('trafficAreaInfoHintExists');
            $form->get('dataTrafficArea')->get('trafficArea')->setValueOptions($this->getTrafficValueOptions());
        }

        return $form;
    }

    /**
     * Remove trailers for PSV
     *
     * @param Form $form
     */
    protected function alterActionForm($form)
    {
        if ($this->isPsv()) {
            $form->get('data')->remove('numberOfTrailers');
            $form->remove('advertisements');

            $label = $form->get('data')->getLabel();
            $form->get('data')->setLabel($label .= '-psv');

            $label = $form->get('data')->get('sufficientParking')->getLabel();
            $form->get('data')->get('sufficientParking')->setLabel($label .= '-psv');

            $label = $form->get('data')->get('permission')->getLabel();
            $form->get('data')->get('permission')->setLabel($label .= '-psv');
        } else {

            $this->processFileUploads(
                array('advertisements' => array('file' => 'processAdvertisementFileUpload')),
                $form
            );

            $fileList = $form->get('advertisements')->get('file')->get('list');

            $bundle = array(
                'properties' => array(
                    'id',
                    'version',
                    'identifier',
                    'fileName',
                    'size'
                )
            );

            $unlinkedFileData = $this->makeRestCall(
                'Document',
                'GET',
                array(
                    'application' => $this->getIdentifier(),
                    'documentCategory' => 1,
                    'documentSubCategory' => 2,
                    'operatingCentre' => 'NULL'
                ),
                $bundle
            );

            $fileData = array();

            if ($this->getActionName() == 'edit') {
                $fileData = $this->actionLoad($this->getActionId())['operatingCentre']['adDocuments'];
            }

            $fileData = array_merge($fileData, $unlinkedFileData['Results']);

            $fileList->setFiles($fileData, $this->url());

            $this->processFileDeletions(array('advertisements' => array('file' => 'deleteFile')), $form);
        }

        return $form;
    }

    /**
     * Get table data
     *
     * @param int $id
     * @return array
     */
    protected function getTableData($id)
    {
        if (is_null($this->tableData)) {
            $data = $this->makeRestCall(
                'ApplicationOperatingCentre',
                'GET',
                array('application' => $id),
                $this->getActionDataBundle()
            );

            $newData = array();

            foreach ($data['Results'] as $row) {

                $newRow = $row;

                if (isset($row['operatingCentre']['address'])) {

                    unset($row['operatingCentre']['address']['id']);
                    unset($row['operatingCentre']['address']['version']);

                    $newRow = array_merge($newRow, $row['operatingCentre']['address']);
                }

                unset($newRow['operatingCentre']);

                $newData[] = $newRow;
            }

            $this->tableData = $newData;
        }

        return $this->tableData;
    }

    /**
     * Save the operating centre
     *
     * @param array $data
     * @param string $service
     * @return null|Response
     */
    protected function actionSave($data, $service = null)
    {
        $saved = parent::actionSave($data['operatingCentre'], 'OperatingCentre');

        if ($this->getActionName() == 'add') {
            if (!isset($saved['id'])) {
                throw new \Exception('Unable to save operating centre');
            }

            $data['applicationOperatingCentre']['operatingCentre'] = $saved['id'];

            $operatingCentreId = $saved['id'];
        } else {
            $operatingCentreId = $data['operatingCentre']['id'];
        }

        if (isset($data['applicationOperatingCentre']['file']['list'])) {
            foreach ($data['applicationOperatingCentre']['file']['list'] as $file) {
                $this->makeRestCall(
                    'Document',
                    'PUT',
                    array('id' => $file['id'], 'version' => $file['version'], 'operatingCentre' => $operatingCentreId)
                );
            }
        }

        if ($this->isPsv()) {
            $data['applicationOperatingCentre']['adPlaced'] = 0;
        }

        $saved = parent::actionSave($data['applicationOperatingCentre'], $service);

        if ($this->getActionName() == 'add' && !isset($saved['id'])) {
            throw new \Exception('Unable to save application operating centre');
        }

        // process Traffic Area
        $licenceData = $this->getLicenceData();
        if ($licenceData['niFlag'] && !$data['trafficArea']['id']) {
            $this->setTrafficArea(self::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
        }
    }

    /**
     * Save method
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        $this->setTrafficArea($data['trafficArea']);
        parent::save($data, $service);
    }

    /**
     * Process the action load data
     *
     * @param array $oldData
     */
    protected function processActionLoad($oldData)
    {
        $data['data'] = $oldData;

        if ($this->getActionName() != 'add') {
            $data['operatingCentre'] = $data['data']['operatingCentre'];
            $data['address'] = $data['operatingCentre']['address'];
            $data['address']['country'] = 'country.' . $data['address']['country'];

            $data['advertisements'] = array(
                'adPlaced' => $data['data']['adPlaced'],
                'adPlacedIn' => $data['data']['adPlacedIn'],
                'dateAdPlaced' => $data['data']['dateAdPlaced']
            );

            unset($data['data']['adPlaced']);
            unset($data['data']['adPlacedIn']);
            unset($data['data']['dateAdPlaced']);
            unset($data['data']['operatingCentre']);
        }

        $data['data']['application'] = $this->getIdentifier();
        if (array_key_exists('application', $oldData) && array_key_exists('licence', $oldData['application']) &&
            array_key_exists('trafficArea', $oldData['application']['licence']) &&
            array_key_exists('id', $oldData['application']['licence']['trafficArea'])) {
            $data['trafficArea']['id'] = $oldData['application']['licence']['trafficArea']['id'];
        }

        return $data;
    }

    /**
     * Process the loading of data
     *
     * @param array $data
     */
    protected function processLoad($oldData)
    {
        $results = $this->getTableData($this->getIdentifier());

        $data['data'] = $oldData;

        $data['data']['noOfOperatingCentres'] = count($results);
        $data['data']['minVehicleAuth'] = 0;
        $data['data']['maxVehicleAuth'] = 0;
        $data['data']['minTrailerAuth'] = 0;
        $data['data']['maxTrailerAuth'] = 0;
        $data['data']['licenceType'] = $this->getLicenceType();
        foreach ($results as $row) {

            $data['data']['minVehicleAuth'] = max(
                array($data['data']['minVehicleAuth'], $row['numberOfVehicles'])
            );
            $data['data']['minTrailerAuth'] = max(
                array($data['data']['minTrailerAuth'], $row['numberOfTrailers'])
            );
            $data['data']['maxVehicleAuth'] += (int) $row['numberOfVehicles'];
            $data['data']['maxTrailerAuth'] += (int) $row['numberOfTrailers'];
        }

        if (array_key_exists('licence', $oldData) && array_key_exists('trafficArea', $oldData['licence']) &&
            array_key_exists('id', $oldData['licence']['trafficArea'])) {
            $data['dataTrafficArea']['hiddenId'] = $oldData['licence']['trafficArea']['id'];
        }
        return $data;
    }

    /**
     * Handle the file upload
     *
     * @param array $file
     */
    protected function processAdvertisementFileUpload($file)
    {
        $this->uploadFile(
            $file,
            array(
                'description' => 'Advertisement',
                'documentCategory' => 1,
                'documentSubCategory' => 2
            )
        );
    }

    /**
     * Get Traffic Area information for current application
     *
     * @return array
     */
    protected function getTrafficArea()
    {
        if (!$this->trafficArea) {
            $bundle = array(
                'properties' => array(
                    'id',
                    'version',
                ),
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'id'
                        ),
                        'children' => array(
                            'trafficArea' => array(
                                'properties' => array(
                                    'id',
                                    'name'
                                )
                            )
                        )
                    )
                )
            );

            $application = $this->makeRestCall(
                'Application',
                'GET',
                array(
                    'id' => $this->getIdentifier(),
                ),
                $bundle
            );
            if (array_key_exists('licence', $application) && count($application['licence']) &&
                array_key_exists('trafficArea', $application['licence']) &&
                count($application['licence']['trafficArea'])
                ) {
                $this->trafficArea = $application['licence']['trafficArea'];
            }
        }
        return $this->trafficArea;
    }

    /**
     * Get Traffic Area value options for select element
     *
     * @return array
     */
    protected function getTrafficValueOptions()
    {
        $bundle = array(
            'properties' => array(
                'id',
                'name',
            ),
        );

        $trafficArea = $this->makeRestCall('TrafficArea', 'GET', array(), $bundle);
        $valueOptions = array();
        $results = $trafficArea['Results'];
        if (is_array($results) && count($results)) {
            usort(
                $results,
                function ($a, $b) {
                    return strcmp($a["name"], $b["name"]);
                }
            );

            // remove Northern Ireland Traffic Area
            foreach ($results as $key => $value) {
                if ($value['id'] == self::NORTHERN_IRELAND_TRAFFIC_AREA_CODE) {
                    unset($results[$key]);
                    break;
                }
            }

            foreach ($results as $element) {
                $valueOptions[$element['id']] = $element['name'];
            }
        }
        return $valueOptions;
    }

    /**
     * Clear Traffic Area if we are deleting last one operating centres
     */
    public function maybeClearTrafficAreaId()
    {
        $bundle = array(
            'properties' => array(
                'id',
                'version'
            )
        );
        $operatingCentres = $this->makeRestCall(
            'ApplicationOperatingCentre',
            'GET',
            array(
                'application' => $this->getIdentifier(),
            ),
            $bundle
        );

        if ($operatingCentres['Count'] == 1) {
            $this->setTrafficArea(array('trafficArea' => null));
        }
    }

    /**
     * Set traffic area to application's licence based on traarea id
     * 
     * @param array $data
     */
    public function setTrafficArea($id = null)
    {
        if ($id) {
            $bundle = array(
                'properties' => array(
                    'id',
                    'version'
                ),
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'id',
                            'version'
                        )
                    )
                )
            );
            $application = $this->makeRestCall('Application', 'GET', array('id' => $this->getIdentifier()), $bundle);
            if (is_array($application) && array_key_exists('licence', $application) &&
                array_key_exists('version', $application['licence'])) {
                $data = array(
                            'id' => $application['licence']['id'],
                            'version' => $application['licence']['version'],
                            'trafficArea' => $id
                );
                $this->makeRestCall('Licence', 'PUT', $data);
            }
        }
    }

    /**
     * Get traffic area by postocde
     * 
     * @param string $postcode
     * @return string
     */
    public function getTrafficAreaByPostcode($postcode = null)
    {
        $retv = null;
        if ($postcode) {
            $response = $this->sendGet('postcode\address', array('postcode' => $postcode), true);
            if (is_array($response) && count($response)) {
                $adminArea = $response[0]['administritive_area'];
                if ($adminArea) {
                    $bundle = array(
                        'properties' => null,
                        'children' => array(
                            'trafficArea' => array(
                                'properties' => array(
                                    'id'
                                )
                            )
                        )
                    );
                    $adminAreaTrafficArea = $this->makeRestCall('AdminAreaTrafficArea', 'GET', array('id' => $adminArea), $bundle);
                    if (array_key_exists('trafficArea', $adminAreaTrafficArea) && count($adminAreaTrafficArea)) {
                        $retv = $adminAreaTrafficArea['trafficArea']['id'];
                    }
                }
            }
        }
        return $retv;
    }
}
