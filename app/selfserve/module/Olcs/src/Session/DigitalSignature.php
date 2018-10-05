<?php

namespace Olcs\Session;

/**
 * Class DigitalSignature
 *
 *
*/
class DigitalSignature extends \Zend\Session\Container
{
    const SESSION_NAME = 'DigitalSignature';
    protected $transportManagerApplicationId;
    protected $transportManagerApplicationOperatorSignature;

    /**
     * DigitalSignature constructor.
     */
    public function __construct()
    {
        parent::__construct(self::SESSION_NAME);
    }

    /**
     * Has Application ID been set
     *
     * @return bool
     */
    public function hasApplicationId()
    {
        return $this->offsetExists('applicationId');
    }

    /**
     * Set Application ID
     *
     * @param int $applicationId Application ID
     *
     * @return void
     */
    public function setApplicationId($applicationId)
    {
        $this->applicationId = $applicationId;
    }

    /**
     * Get Application ID
     *
     * @return int
     */
    public function getApplicationId()
    {
        return (int) $this->applicationId;
    }

    /**
     * Has ContinuationDetail ID been set
     *
     * @return bool
     */
    public function hasContinuationDetailId()
    {
        return $this->offsetExists('continuationDetailId');
    }

    /**
     * Set ContinuationDetail ID
     *
     * @param int $continuationDetailId ContinuationDetail ID
     *
     * @return void
     */
    public function setContinuationDetailId($continuationDetailId)
    {
        $this->continuationDetailId = $continuationDetailId;
    }

    /**
     * Get ContinuationDetail ID
     *
     * @return int
     */
    public function getContinuationDetailId()
    {
        return (int) $this->continuationDetailId;
    }

    public function setTransportManagerApplicationOperatorSignature($transportManagerApplicationId)
    {
        $this->setTransportManagerApplicationId($transportManagerApplicationId);
        $this->transportManagerApplicationOperatorSignature = true;
    }

    public function setTransportManagerApplicationId($transportManagerApplicationId)
    {
        $this->transportManagerApplicationId = $transportManagerApplicationId;
    }

    public function hasTransportManagerApplicationId()
    {
        return $this->offsetExists('transportManagerApplicationId');
    }
}
