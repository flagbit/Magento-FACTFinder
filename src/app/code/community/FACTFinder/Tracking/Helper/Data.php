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
     * Get the correct path where the tracking should be sent
     *
     * @return string
     */
    public function getTrackingUrlPath()
    {
        return 'ff_tracking/proxy/tracking';
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


    /**
     * Get id field name for tracking
     *
     * @return bool
     */
    public function getIdFieldName()
    {
        return Mage::getStoreConfig('factfinder/config/tracking_identifier');
    }


}
