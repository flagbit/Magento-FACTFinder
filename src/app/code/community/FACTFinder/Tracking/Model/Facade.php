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
 * Model class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
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
