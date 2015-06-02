<?php

class FACTFinder_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Redirect to product page
     *
     * @param \Mage_Catalog_Model_Product $product
     */
    public function redirectToProductPage(Mage_Catalog_Model_Product $product)
    {
        $response = Mage::app()->getResponse();
        $response->setRedirect($product->getProductUrl());
        $response->sendResponse();
        exit;
    }


    /**
     * Check if redirect to product page for single result
     *
     * @return bool
     */
    public function isRedirectForSingleResult()
    {
        return (bool)Mage::app()->getStore()->getConfig('factfinder/config/redirectOnSingleResult');
    }


    /**
     * Check if the module is enabled
     *
     * @param string|null $module
     *
     * @return bool
     */
    public function isEnabled($module = null)
    {
        $result = (bool)Mage::app()->getStore()->getConfig('factfinder/search/enabled');
        if ($module !== null) {
            $result &= (bool)Mage::app()->getStore()->getConfig('factfinder/modules/' . $module);
        }

        return $result;
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
     *
     * @param string $module
     * @param bool $isActive
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
            $item->nodeValue = $isActive;
        }

        $xml->save($file);
    }

}
