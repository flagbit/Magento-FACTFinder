<?php
class FACTFinder_Tracking_Model_Facade extends FACTFinder_Core_Model_Facade
{
    public function getTrackingAdapter($channel = null)
    {
        return $this->_getAdapter("scicTracking", $channel);
    }

    public function getLegacyTrackingAdapter($channel = null)
    {
        return $this->_getAdapter("legacyTracking", $channel);
    }

    public function configureTrackingAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "tracking", $channel, $id);
    }

    public function applyTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("tracking", "applyTracking", $channel, $id);
    }

    public function applyScicTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("scic", "applyTracking", $channel, $id);
    }

    public function applyLegacyTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("legacyTracking", "applyTracking", $channel, $id);
    }

    public function getScicAdapter($channel = null)
    {
        return $this->_getAdapter("scic", $channel);
    }

    public function configureScicAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "scic", $channel, $id);
    }

}