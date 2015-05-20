<?php
class FACTFinder_Tracking_Model_Facade extends FACTFinder_Core_Model_Facade
{
    public function getTrackingAdapter($channel = null)
    {
        return $this->_getAdapter("tracking", $channel);
    }

    public function getScicAdapter($channel = null)
    {
        return $this->_getAdapter("scicTracking", $channel);
    }
}