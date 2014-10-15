<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class EbsrPack
 * @package Olcs\Service\Data
 */
class EbsrPack extends AbstractData
{
    /**
     * @var \Zend\InputFilter\Input
     */
    protected $validationChain;

    /**
     * @var string
     */
    protected $serviceName = 'ebsr\pack';

    /**
     * @param \Zend\InputFilter\Input $validationChain
     * @return $this
     */
    public function setValidationChain($validationChain)
    {
        $this->validationChain = $validationChain;
        return $this;
    }

    /**
     * @return \Zend\InputFilter\Input
     */
    public function getValidationChain()
    {
        return $this->validationChain;
    }

    /**
     * Temp stub method
     * @return array
     */
    public function fetchPackList()
    {
        return [
            [
                'status' => 'Recieved',
                'filename' => 'PB000679.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Processing',
                'filename' => 'PB000678.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Distributing',
                'filename' => 'PB000677.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Complete',
                'filename' => 'PB000676.zip',
                'submitted' => '2014-10-07'
            ]

        ];
    }

    public function processPackUpload($data)
    {
        // put this stuff into a factory possibly; add more validators as needed.
        $validator = $this->getValidationChain();

        $dir = new \FilesystemIterator(
            $data['fields']['file']['extracted_dir'],
            \FilesystemIterator::CURRENT_AS_PATHNAME
        );

        $packs = [];

        foreach ($dir as $ebsrPack) {
            $validator->setValue($ebsrPack);
            if ($validator->isValid()) {
                $packs[] = $ebsrPack;
            }
        }

        if ($this->sendPackList($packs)) {
            return count($packs);
        }

        return false;
    }

    public function sendPackList($packs)
    {
        //notify ebsr service about packs
        return true;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        parent::createService($serviceLocator);

        $this->setValidationChain($serviceLocator->get('Olcs\InputFilter\EbsrPackInput'));

        return $this;
    }
}
