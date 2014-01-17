<?php
/**
 * Tracking adapter using the old tracking API with FACT-Finder 6.8 or 6.9
 */
class FACTFinder_Http_LegacyTrackingAdapter extends FACTFinder_Http_ScicAdapter
{
    /**
     * Set up the old tracking adapter with the new Action path it has
     */
    protected function init()
    {
        parent::init();
        $this->getDataProvider()->setType('Tracking.ff');
    }
}
