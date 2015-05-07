<?php

class FACTFinder_Tracking_Model_Handler_Tracking extends FACTFinder_Core_Model_Handler_Abstract
{

    protected $_facadeModel = 'factfinder_tracking/facade';

    /**
     * @var array
     */
    protected $_trackingAdapter;


    /**
     * Just a stub
     */
    protected function _configureFacade()
    {
    }

    /**
     * Get tracking adapter
     *
     * @return Object
     */
    public function getTrackingAdapter()
    {
        if ($this->_trackingAdapter === null) {
            if (Mage::helper('factfinder_tracking')->useOldTracking()) {
                $this->_trackingAdapter = Mage::getModel('factfinder_tracking/handler_tracking_scic');
            }
            // If old tracking was not activated use the new tracking
            if ($this->_trackingAdapter === null) {
                $this->_trackingAdapter = $this->_getFacade()->getTrackingAdapter();
            }
            if ($this->_trackingAdapter === null) {
                $this->_trackingAdapter = array();
            }
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
        if (Mage::helper('factfinder')->useOldTracking()) {
            if (Mage::helper('factfinder')->useLegacyTracking()) {
                $result = $this->_getFacade()->applyLegacyTracking();
            } else {
                $result = $this->_getFacade()->applyScicTracking();
            }
        } else {
            $result = $this->_getFacade()->applyTracking();
        }

        return $result;
    }
}