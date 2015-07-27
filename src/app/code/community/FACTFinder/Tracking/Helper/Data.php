<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Helper class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
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
     * Get the correct path where the tracking should be sent
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

    /**
     * Get session id which was initialy saved at session start
     *
     * @return string
     */
    public function getSessionId()
    {
        return md5(Mage::getSingleton('customer/session')->getData('ff_session_id'));
    }


}
