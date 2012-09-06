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
    const XML_CONFIG_PATH = 'factfinder/search';

    private $config;
    private $authType;
	private $secondaryChannels;
    private $storeId = null;

    public function __construct($config = null)
    {
    	$this->config = new Varien_Object($config);
    	if(is_array($config)){
    		$this->config->setData($config);
    	} else {
			$this->config->setData(Mage::getStoreConfig(self::XML_CONFIG_PATH));
		}
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->getCustomValue('version');
    }

    /**
     * @return boolean
     */
    public function isDebugEnabled() {
        return $this->getCustomValue('debug') == 'true';
    }

    /**
     * @param string name
     * @return string value
     */
    public function getCustomValue($name)
    {
    	if(!$this->config->hasData($name)){
    		try{
    			$this->config->setData($name,  Mage::getStoreConfig(self::XML_CONFIG_PATH.'/'.$name, $this->storeId));
    		}catch (Exception $e){
    			$this->config->setData($name, null);
    		}
    	}
    	return $this->config->getData($name);
    }

    public function __sleep() {

    	foreach(get_class_methods($this) as $method){
    		if(substr($method, 0, 3) != 'get'
    			|| $method == 'getCustomValue'){
    			continue;
    		}
    		call_user_func(array(&$this, $method));
    	}
    	return array('config');
    }

    /**
	 * @deprecated because of wrong spelling; use getRequestProtocol() instead
     * @return string
     */
    public function getRequestProtokoll() {
    	return $this->getRequestProtocol();
    }

    /**
     * @return string
     */
	public function getRequestProtocol() {
		$protocol = $this->getCustomValue('protocol');
		// legacy code because of wrong spelling
		if (!$protocol) {
			$protocol = $this->getCustomValue('protokoll');
		}
		return $protocol;
	}

    /**
     * @return string
     */
    public function getServerAddress() {
		return $this->getCustomValue('address');
    }

    /**
     * @return int
     */
    public function getServerPort() {
		return $this->getCustomValue('port');
    }

    /**
     * @return string
     */
    public function getContext() {
		return $this->getCustomValue('context');
    }
	
	/**
	 * @return string
	 **/
	public function getFactFinderVersion() {
		return $this->getCustomValue('ffversion');
	}

    /**
     * @return string
     */
    public function getChannel() {
        return $this->getCustomValue('channel');
    }
	
	/**
	 * @return array of strings
	 **/
	public function getSecondaryChannels() {
		if($this->secondaryChannels == null)
		{
			// array_filter() is used to remove empty channel names
			$this->secondaryChannels = array_filter(explode(';', $this->getCustomValue('secondary_channels')));
		}
		return $this->secondaryChannels;
	}

    /**
     * @return string
     */
    public function getLanguage() {
		return $this->getCustomValue('language');
    }

    /**
     * @return string
     */
    public function getAuthUser() {
		return $this->getCustomValue('auth_user');
    }

    /**
     * @return string
     */
    public function getAuthPasswort() {
		return $this->getCustomValue('auth_password');
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
            $this->authType = $this->getCustomValue('auth_type');
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
    	return $this->getCustomValue('auth_advancedPrefix');
    }

    /**
     * @return string
     */
    public function getAdvancedAuthPostfix(){
		return $this->getCustomValue('auth_advancedPostfix');
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
    }

    /**
     * {@inheritdoc}
     *
     * @return array with string as key and boolean true as value for each of them
     */
    public function getIgnoredPageParams() {
        return array(
			'channel' => true,
			'format' => true,
			'log' => true,
			'productsPerPage' => true,
			'query' => true
		);
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
    	return $this->getCustomValue('pageContent');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    function getPageUrlEncoding() {
    	return $this->getCustomValue('pageURI');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    function getServerUrlEncoding() {
    	return $this->getCustomValue('serverURI');
    }


    /**
     * Allows to catch configuration for certain store id.
     * If given store id differs from internal store id, then configuration is cleared.
     *
     * @param int $storeId
     * @return FACTFinderCustom_Configuration
     */
    public function setStoreId($storeId) {
        if ($this->storeId != $storeId) {
            $this->config = new Varien_Object();
        }

        $this->storeId = $storeId;

        return $this;
    }
}