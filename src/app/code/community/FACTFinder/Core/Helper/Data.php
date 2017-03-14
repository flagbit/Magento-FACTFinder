<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Helper class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    const SKIP_FF_PARAM_NAME = 'skip_ff';
    const USE_FALLBACK_CONFIG_PATH = 'factfinder/fallback/use_fallback';
    const PRIMARY_CHANNEL_CONFIG_PATH = 'factfinder/search/channel';


    /**
     * Check if the module is enabled
     *
     * @param string|null $feature
     * @param null|int    $storeId
     *
     * @return bool
     */
    public function isEnabled($feature = null, $storeId = null)
    {
        if ($this->_getRequest()->getParam(self::SKIP_FF_PARAM_NAME)
            && Mage::getStoreConfig(self::USE_FALLBACK_CONFIG_PATH, $storeId)
        ) {
            return false;
        }

        $result = (bool) Mage::app()->getStore($storeId)->getConfig('factfinder/search/enabled');
        if ($feature !== null) {
            $result &= (bool) Mage::app()->getStore($storeId)->getConfig('factfinder/modules/' . $feature);
        }

        return $result;
    }


    /**
     * Check if the sub-module was activated in config
     *
     * @param string $module
     *
     * @return bool
     */
    public function isModuleActivated($module)
    {
        return (bool) Mage::app()->getStore()->getConfig('advanced/ff_modules/' . $module);
    }


    /**
     * Check if the request is from internal IP address
     *
     * @return bool
     */
    public function isInternal()
    {
        $internalIp = Mage::getStoreConfig('factfinder/config/internal_ip');
        $currentIp = Mage::helper('core/http')->getRemoteAddr();

        return strpos($internalIp, $currentIp) !== false;
    }


    /**
     * Redirect to the same url but with the skip parameter
     *
     * @return void
     */
    public function performFallbackRedirect()
    {
        if (!$this->_getRequest()->getParam(self::SKIP_FF_PARAM_NAME)
            && Mage::getStoreConfig(self::USE_FALLBACK_CONFIG_PATH)
        ) {
            $url = Mage::helper('core/url')->getCurrentUrl();
            $url .= strpos($url, '?') ? '&' : '?';
            $url .= self::SKIP_FF_PARAM_NAME . '=1';
            Mage::app()->getResponse()->setRedirect($url)
                ->sendResponse();
            exit(0);
        }
    }


    /**
     * Get primary channel for store
     *
     * @param null|int $storeId
     *
     * @return string|null
     */
    public function getPrimaryChannel($storeId = null)
    {
        return Mage::getStoreConfig(self::PRIMARY_CHANNEL_CONFIG_PATH, $storeId);
    }


}
