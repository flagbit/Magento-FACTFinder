<?php

class FACTFinder_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Check if the module is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::app()->getStore()->getConfig('factfinder/search/enabled');
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
     * Check is the specified module was enabled in config
     *
     * @param $name
     *
     * @return bool
     */
    public function isModuleActivated($name)
    {
        return (bool) Mage::getStoreConfig('factfinder/modules/' . $name);
    }

}