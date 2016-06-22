<?php
/**
 * FACTFinder_Suggest
 *
 * @category Mage
 * @package FACTFinder_Suggest
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
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_CONFIG_PATH_USE_PROXY = 'factfinder/config/proxy';
    const EXPORT_TRIGGER_DELAY = 90;


    /**
     * Get FACT-Finder Suggest URL
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        if ($this->isSuggestProxyActivated()) {
            $params = array();
            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $params['_secure'] = true;
            }

            $url = $this->_getUrl('factfinder_suggest/proxy/suggest', $params);
        } else {
            $url = Mage::getSingleton('factfinder_suggest/facade')->getSuggestUrl();
            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $url = preg_replace('/^http:/', 'https:', $url);
            }
        }

        // avoid specifying the default port for http
        $url = preg_replace('/^(http:[^\:]+)\:80\//', "$1/", $url);

        return $url;
    }


    /**
     * Check config is proxy is activated
     *
     * @return bool
     */
    public function isSuggestProxyActivated()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_USE_PROXY);
    }


    /**
     * Check if import should be triggered for store
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function shouldTriggerImport($storeId)
    {
        if (!Mage::getStoreConfigFlag('factfinder/modules/suggest', $storeId)) {
            return false;
        }

        return Mage::getStoreConfigFlag('factfinder/export/trigger_suggest_import', $storeId);
    }


    /**
     * Trigger suggest import in a separate delayed process
     *
     * @param int $storeId
     *
     * @return void
     */
    public function triggerDelayedImport($storeId)
    {
        $pid = pcntl_fork();
        if (!$pid) {
            $channel = Mage::helper('factfinder')->getPrimaryChannel($storeId);
            $facade = Mage::getModel('factfinder_suggest/facade');
            sleep(self::EXPORT_TRIGGER_DELAY);
            $facade->triggerSuggestImport($channel);
            exit(0);
        }
    }


}
