<?php

/**
 * this class implements the FACTFinder configuration interface and uses the Zend_Config class. so it's like a decorator
 * for the Zend_Config
 *
 * @package FACTFinder\Common
 */
class FACTFinderCustom_Configuration implements FACTFinder_Abstract_Configuration
{    
    const HTTP_AUTH     = 'http';
    const SIMPLE_AUTH   = 'simple';
    const ADVANCED_AUTH = 'advanced';
    
    private $config;
    private $authType;
    private $pageMappings;
    private $serverMappings;
    private $pageIgnores;
    private $serverIgnores;
    private $requiredPageParams;
    private $requiredServerParams;
    
    
    /**
     * @return string
     */
    public function getVersion() {
        return Mage::getStoreConfig('factfinder/search/version');
    }
    
    /**
     * @return boolean
     */
    public function isDebugEnabled() {
        return Mage::getStoreConfig('factfinder/search/debug') == 'true';
    }
    
    /**
     * @param string name
     * @return string value
     */
    public function getCustomValue($name) {
        return Mage::getStoreConfig('factfinder/'.$name);
    }
    
    /**
     * @return string
     */
    public function getRequestProtokoll() {
    	return Mage::getStoreConfig('factfinder/search/protokoll');
    }
    
    /**
     * @return string
     */
    public function getServerAddress() {
		return Mage::getStoreConfig('factfinder/search/address');    	
    }
    
    /**
     * @return int
     */
    public function getServerPort() {
		return Mage::getStoreConfig('factfinder/search/port');    	
    }
    
    /**
     * @return string
     */
    public function getContext() {
		return Mage::getStoreConfig('factfinder/search/context');    	
    }
    
    /**
     * @return string
     */
    public function getChannel() {
        return Mage::getStoreConfig('factfinder/search/channel');
    }
    
    /**
     * @return string
     */
    public function getLanguage() {
		return Mage::getStoreConfig('factfinder/search/language');    	
    }
    
    /**
     * @return string
     */
    public function getAuthUser() {
		return Mage::getStoreConfig('factfinder/search/auth_user');    	
    }
    
    /**
     * @return string
     */
    public function getAuthPasswort() {
		return Mage::getStoreConfig('factfinder/search/auth_password');    	
    }
    
    /**
     * @return boolean
     */
    public function isHttpAuthenticationType() {
        return $this->getAuthType() == self::HTTP_AUTH;
    }
        
    /**
     * @return boolean
     */
    public function isSimpleAuthenticationType() {
        return $this->getAuthType() == self::SIMPLE_AUTH;
    }
    
    /**
     * @return boolean
     */
    public function isAdvancedAuthenticationType() {
        return $this->getAuthType() == self::ADVANCED_AUTH;
    }
    
    private function getAuthType() {
        if ($this->authType == null) {
            $this->authType = Mage::getStoreConfig('factfinder/search/auth_type');
            if ( $this->authType != self::HTTP_AUTH
                    && $this->authType != self::SIMPLE_AUTH
                    && $this->authType != self::ADVANCED_AUTH ) {
                $this->authType = self::HTTP_AUTH;
            }
        }
        return $this->authType;
    }
    
    /**
     * @return string
     */
    public function getAdvancedAuthPrefix() {
    	return Mage::getStoreConfig('factfinder/search/auth_advancedPrefix');
    }
    
    /**
     * @return string
     */
    public function getAdvancedAuthPostfix(){
		return Mage::getStoreConfig('factfinder/search/auth_advancedPostfix');    	
    }
    
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getPageMappings() {
        return array();
    }
    
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getServerMappings() {
    	return array();
        return array(
        	'q' => 'query',
        	'p' => 'page',
        );
    }
    
    /**
     * {@inheritdoc}
     *
     * @return array with string as key and boolean true as value for each of them
     */
    public function getIgnoredPageParams() {
        return array();
    }
    
    /**
     * {@inheritdoc}
     *
     * @return array with string as key and boolean true as value for each of them
     */
    public function getIgnoredServerParams() {
        return array();
    }
    
    /**
     * {@inheritdoc}
     *
     * @return array string to string map (param-name as array-key; default value as array-value)
     */
    public function getRequiredPageParams(){
        return array();
    }
    
    /**
     * {@inheritdoc}
     *
     * @return array string to string map (param-name as array-key; default value as array-value)
     */
    function getRequiredServerParams(){
        return array();
    }
    
    
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    function getPageContentEncoding() {
    	return Mage::getStoreConfig('factfinder/encoding/pageContent');
    }
    
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    function getPageUrlEncoding() {
    	return Mage::getStoreConfig('factfinder/encoding/pageURI');
    }
    
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    function getServerUrlEncoding() {
    	return Mage::getStoreConfig('factfinder/encoding/serverURI');
    }
}