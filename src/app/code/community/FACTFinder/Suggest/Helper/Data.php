<?php

class FACTFinder_Suggest_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @var string
     */
    const XML_CONFIG_PATH_USE_PROXY = 'factfinder/config/proxy';


    /**
     * get FACT-Finder Suggest URL
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
            $url = Mage::getSingleton('factfinder/facade')->getSuggestUrl();
            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $url = preg_replace('/^http:/', 'https:', $url);
            }
        }
        return $url;
    }

    /**
     * @return bool
     */
    public function isSuggestProxyActivated()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_USE_PROXY);
    }
}