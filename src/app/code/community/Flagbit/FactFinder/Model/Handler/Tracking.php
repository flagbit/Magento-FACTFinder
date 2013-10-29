<?php
/**
 * Handles tracking data
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2013 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Nicolai Essig <nicolai.essig@flagbit.de>
 * @version     $Id: Tracking.php 20.08.13 11:35 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_Tracking
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    /**
     * @var array
     */
    protected $_trackingAdapter;

    /**
     * {@inheritdoc}
     */
    protected function configureFacade() {}

    /**
     * Get tracking adapter
     *
     * @return tracking adapter object
     */
    public function getTrackingAdapter()
    {
        if($this->_trackingAdapter === null)
        {
            if(Mage::helper('factfinder')->useOldTracking()) {
                $this->_trackingAdapter = Mage::getModel('factfinder/handler_tracking_scic');
            }
            // If old tracking was not activated use the new tracking
            if ($this->_trackingAdapter === null) {
                $this->_trackingAdapter = $this->_getFacade()->getTrackingAdapter();
            }
            if ($this->_trackingAdapter === null)
                $this->_trackingAdapter = array();
        }
        return $this->_trackingAdapter;
    }

    /**
     * Fire tracking request
     *
     * @return mixed|null
     */
    public function applyTracking()
    {
        if(Mage::helper('factfinder')->useOldTracking()) {
            $result =  $this->_getFacade()->applyScicTracking();
        } else {
            $result = $this->_getFacade()->applyTracking();
        }

        return $result;
    }
}