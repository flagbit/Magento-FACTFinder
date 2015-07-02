<?php

class FACTFinder_Asn_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * Parse url and return array of parameters
     *
     * @param string $url
     *
     * @return array
     */
    public function getQueryParams($url)
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
     * Check is catalog navigation replacement is enabled
     *
     * @return bool
     */
    public function isCatalogNavigation()
    {
        return (bool) Mage::app()->getStore()->getConfig('factfinder/modules/catalog_navigation');
    }


}