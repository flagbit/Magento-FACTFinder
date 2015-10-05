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

    /**
     * Parse url and return array of parameters
     *
     * @param string $url
     *
     * @return array
     */
    function getQueryParams($url)
    {
        $queryParams = array();

        //conserve url encoded spaces, since parse_str replaces them with underscores
        $url = str_replace('%20', 'XXX', $url);

        $parseUrl = parse_url($url);
        if (isset($parseUrl['query'])) {
            parse_str($parseUrl['query'], $queryParams);
        }

        // recover spaces
        // we use not encoded values since they will be encoded with Mage::getUrl()
        $result = array();
        foreach ($queryParams as $key => $value) {
            $key = str_replace('XXX', ' ', $key);
            $value = str_replace('XXX', ' ', $value);
            $result[$key] = $value;
        }


        return $result;
    }


    /**
     * Retrieve from config category ids that should not be exported
     *
     * @return array
     */
    public function getCategoryIdToExclude()
    {
        if (empty($this->_categoryIdsToExclude)) {
            $categoryIds = Mage::getStoreConfig('factfinder/export/exclude_category_ids');
            $this->_categoryIdsToExclude = explode(',', $categoryIds);
        }

        return $this->_categoryIdsToExclude;
    }


}