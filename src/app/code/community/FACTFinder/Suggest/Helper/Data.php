<?php
/**
 * FACTFinder_Suggest
 *
 * @category Mage
 * @package FACTFinder_Suggest
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
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_CONFIG_PATH_USE_PROXY = 'factfinder/config/proxy';
    const IMPORT_TYPE = 'suggest';


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

            // remove all parameters except for channel
            $url = $this->removeUrlParams($url, array('channel'));
        }

        // avoid specifying the default port for http
        $url = preg_replace('/^(http:[^\:]+)\:80\//', "$1/", $url);
		
		// replace param "query"
		$urlData = parse_url($url);
		$queryString = '';
		if (isset($urlData['query'])) {
			parse_str($urlData['query'], $urlParams);
			unset($urlParams['query']);
			$queryString = http_build_query($urlParams);
		}
		$url = sprintf('%s://%s%s?%s', $urlData['scheme'], $urlData['host'], $urlData['path'], $queryString);

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
     * Trigger suggest import
     *
     * @param int $storeId
     *
     * @return void
     */
    public function triggerImport($storeId)
    {
        $exportHelper = Mage::helper('factfinder/export');
        $channel = Mage::helper('factfinder')->getPrimaryChannel($storeId);
        /** @var FACTFinder_Suggest_Model_Facade $facade */
        $facade = Mage::getModel('factfinder_suggest/facade');
        $facade->setStoreId($storeId);
        $download = !$exportHelper->useFtp($storeId);
        $delay = $exportHelper->getImportDelay(self::IMPORT_TYPE);

        if ($exportHelper->isImportDelayEnabled($storeId)) {
            $pid = pcntl_fork();
            if (!$pid) {
                sleep($delay);
                $facade->triggerSuggestImport($channel, $download);
                exit(0);
            }
        } else {
            $facade->triggerSuggestImport($channel, $download);
        }
    }


    /**
     * Remove all parameters from url except for specified
     *
     * @param string $url
     * @param array  $exclude
     *
     * @return string
     */
    protected function removeUrlParams($url, $exclude = array())
    {
        $excludeParams = array();

        foreach ($exclude as $paramName) {
            preg_match("/[&|?]{$paramName}=([^&]*)&?/", $url, $values);
            if (isset($values[1])) {
                $excludeParams[$paramName] = $values[1];
            }
        }

        $url = strtok($url, '?');
        $query = http_build_query($excludeParams);

        return $url . '?' . $query;
    }


}
