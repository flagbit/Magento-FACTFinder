<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
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
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    const SKIP_FF_PARAM_NAME = 'skip_ff';


    /**
     * Check if the module is enabled
     *
     * @param string|null $feature
     *
     * @return bool
     */
    public function isEnabled($feature = null)
    {
        if ($this->_getRequest()->getParam('skip_ff')) {
            return false;
        }

        $result = (bool) Mage::app()->getStore()->getConfig('factfinder/search/enabled');
        if ($feature !== null) {
            $result &= (bool) Mage::app()->getStore()->getConfig('factfinder/modules/' . $feature);
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
     * Update xml file in etc/modules according to backend config
     * Return true, if the file was written and false if an error occurred
     *
     * @param string $module
     * @param bool   $isActive
     *
     * @return bool
     */
    public function updateModuleState($module, $isActive = true)
    {
        $dir = Mage::getBaseDir('etc') . DS . 'modules' . DS;
        $file = $dir . $module . '.xml';

        if (!file_exists($file)) {
            return;
        }

        $xml = new DOMDocument();
        $xml->load($file);
        foreach ($xml->getElementsByTagName('active') as $item) {
            $item->nodeValue = $isActive ? 'true' : 'false';
        }

        // silencing is not good, but we only need the result of the operation
        return (bool) @$xml->save($file);
    }


    /**
     * Redirect to the same url but with the skip parameter
     *
     * @return void
     */
    public function performFallbackRedirect()
    {
        if (!$this->_getRequest()->getParam(self::SKIP_FF_PARAM_NAME)
            && Mage::getStoreConfig('factfinder/fallback/use_fallback')
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
     * Check whether import must be triggered
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function isImportTriggerEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag('factfinder/export/trigger_data_import', $storeId);
    }


    /**
     * Get list of channels for store
     *
     * @param null|int $storeId
     *
     * @return array
     */
    public function getStoreChannels($storeId = null)
    {
        $primary = Mage::getStoreConfig('factfinder/search/channel', $storeId);
        $secondary = Mage::getStoreConfig('factfinder/search/secondary_channels', $storeId);
        $secondary = explode(';', $secondary);

        return array_merge(array($primary), $secondary);
    }


}
