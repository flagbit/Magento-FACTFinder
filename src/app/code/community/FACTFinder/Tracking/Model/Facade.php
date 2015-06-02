<?php
class FACTFinder_Tracking_Model_Facade extends FACTFinder_Core_Model_Facade
{


    /**
     * Get instance of tracking adapter
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\AbstractAdapter
     */
    public function getTrackingAdapter($channel = null)
    {
        return $this->_getAdapter("tracking", $channel);
    }


    /**
     * Get instance of SCIC adapter
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\AbstractAdapter
     */
    public function getScicAdapter($channel = null)
    {
        return $this->_getAdapter("scicTracking", $channel);
    }


}
