<?php
/**
 * Tracking adapter using the new tracking API introduced with FF 6.9.
 */
class FACTFinder_Http_TrackingAdapter extends FACTFinder_Default_TrackingAdapter
{
    /**
     * Set up the tracking adapter for 6.9.
     */
    protected function init()
    {
        $this->log->info("Initializing new Tracking adapter.");
        $this->getDataProvider()->setType('Tracking.ff');
        $this->getDataProvider()->setCurlOptions(array(
            CURLOPT_CONNECTTIMEOUT => $this->getDataProvider()->getConfig()->getScicConnectTimeout(),
            CURLOPT_TIMEOUT => $this->getDataProvider()->getConfig()->getScicTimeout()
        ));
    }

    /**
     * Trigger the actual tracking request.
     *
     * @return boolean $success
     */
    public function applyTracking()
    {
        // Is this even correct for the new interface?
        $success = trim($this->getData());
        return $success == 'The event was successfully tracked';
    }
}
