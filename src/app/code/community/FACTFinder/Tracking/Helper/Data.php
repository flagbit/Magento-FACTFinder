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
     * Decide whether legacy tracking should be used (old tracking for the versions for 6.8 and 6.9)
     *
     * @return bool
     */
    public function useLegacyTracking()
    {
        $ffVersion = Mage::getStoreConfig('factfinder/search/ffversion');
        return ($ffVersion >= 68 && $ffVersion <= 69);
    }

    /**
     * returns the correct path where the tracking should be sent
     *
     * @return string
     */
    public function getTrackingUrlPath()
    {
        $urlPath = 'factfinder/proxy/tracking';
        if ($this->useOldTracking() && !$this->useLegacyTracking()) {
            // if old tracking is legacy tracking, don't use the scic url
            $urlPath = 'factfinder/proxy/scic';
        }
        return $urlPath;
    }
}