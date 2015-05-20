<?php

class FACTFinder_Tracking_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Decide whether old tracking should be used
     *
     * @return bool
     */
    public function useOldTracking()
    {
        $ffVersion = Mage::getStoreConfig('factfinder/search/ffversion');
        // to use the new tracking, change the comparison to '$ffVersion < 69'
        return ($ffVersion <= 69);
    }

    /**
     * returns the correct path where the tracking should be sent
     *
     * @return string
     */
    public function getTrackingUrlPath()
    {
        $urlPath = 'ff_tracking/proxy/tracking';
        if ($this->useOldTracking()) {
            $urlPath = 'ff_tracking/proxy/scic';
        }
        return $urlPath;
    }
}