<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Helper class
 *
 * This helper class provides Translation Methods throw Magento Helper Abstract
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * returns Module Status by Module Code
	 *
	 * @param string $code Module Code
	 * @return boolean
	 */
    public function isModuleActive($code)
    {
        $module = Mage::getConfig()->getNode("modules/$code");
        $model = Mage::getConfig()->getNode("global/models/$code");
        return $module && $module->is('active') || $model;
    }

    /**
     * Decide whether old tracking should be used (for every FF version up to 6.7)
     *
     * @deprecated use Flagbit_FactFinder_Model_Handler_Tracking::useOldTracking() instead
     * @return bool
     */
    public function useOldTracking()
    {
        return Mage::getModel('factfinder/handler_tracking')->useOldTracking();
    }

    /**
     * Decide whether legacy tracking should be used (for the FF versions 6.8 and 6.9)
     *
     * @deprecated use Flagbit_FactFinder_Model_Handler_Tracking::useLegacyTracking() instead
     * @return bool
     */
    public function useLegacyTracking()
    {
        return Mage::getModel('factfinder/handler_tracking')->useLegacyTracking();
    }

    /**
     * returns the correct path where the tracking should be sent
     *
     * @return string
     */
    public function getTrackingUrlPath()
    {
        $trackingHandler = Mage::getModel('factfinder/handler_tracking');
        $urlPath = 'factfinder/proxy/tracking';
        if ($trackingHandler->useOldTracking() && !$trackingHandler->useLegacyTracking()) {
            // if old tracking is legacy tracking, don't use the scic url
            $urlPath = 'factfinder/proxy/scic';
        }
        return $urlPath;
    }
}
