<?php

class FACTFinder_Asn_Helper_Data extends Mage_Core_Helper_Abstract
{
    function getQueryParams($url)
    {
        $queryParams = array();
        $parseUrl = parse_url($url);
        if (isset($parseUrl['query'])) {
            parse_str($parseUrl['query'], $queryParams);
        }

        return $queryParams;
    }

    /**
     * Get Module Status depending on Module
     *
     * @return boolean
     */
    public function getIsOnSearchPage()
    {
        $moduleName = Mage::app()->getRequest()->getModuleName();
        if ($moduleName == 'catalogsearch'
            || ($moduleName == 'xmlconnect' && strpos(Mage::app()->getRequest()->getActionName(), 'search') !== false)
        ) {
            return true;
        }

        return false;
    }
}