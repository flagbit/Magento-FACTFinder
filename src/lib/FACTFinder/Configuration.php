<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this class implements the FACTFinder configuration interface and uses the Zend_Config class. so it's like a decorator
 * for the Zend_Config
 *
 * @package FACTFinder\Common
 */
class FACTFinder_Configuration implements FACTFinder_Abstract_Configuration
{
    const HTTP_AUTH     = 'http';
    const SIMPLE_AUTH   = 'simple';
    const ADVANCED_AUTH = 'advanced';

    protected $zendConfig;
    private $authType;
    private $pageMappings;
    private $serverMappings;
    private $pageIgnores;
    private $serverIgnores;
    private $requiredPageParams;
    private $requiredServerParams;

    public function __construct(Zend_Config $config)
    {
        $this->zendConfig = $config;
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->zendConfig->version;
    }

    /**
     * @return boolean
	 * @deprecated
     */
    public function isDebugEnabled() {
        return $this->zendConfig->debug == 'true';
    }

    /**
     * @param string name
     * @return string value
     */
    public function getCustomValue($name) {
        return $this->zendConfig->$name;
    }

    /**
	 * @deprecated because of wrong spelling. use "getRequestProtocol()" instead
     * @return string
     */
    public function getRequestProtokoll() {
        return $this->zendConfig->search->protokoll;
    }

	/**
     * @return string
     */
    public function getRequestProtocol() {
        $protocol = $this->zendConfig->search->protocol;

		// legacy code for older configurations
		if (empty($protocol)) {
			$protocol = $this->getRequestProtokoll();
		}
		return $protocol;
    }

    /**
     * @return string
     */
    public function getServerAddress() {
        return $this->zendConfig->search->address;
    }

    /**
     * @return int
     */
    public function getServerPort() {
        return $this->zendConfig->search->port;
    }

    /**
     * @return string
     */
    public function getContext() {
        return $this->zendConfig->search->context;
    }

    /**
     * @return string
     */
    public function getChannel() {
        return $this->zendConfig->search->channel;
    }

    /**
     * @return string
     */
    public function getLanguage() {
        return $this->zendConfig->search->language;
    }

    /**
     * @return string
     */
    public function getAuthUser() {
        return $this->zendConfig->search->auth->user;
    }

    /**
     * @return string
     */
    public function getAuthPasswort() {
        return $this->zendConfig->search->auth->password;
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
            $this->authType = $this->zendConfig->search->auth->type;
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
        return $this->zendConfig->search->auth->advancedPrefix;
    }

    /**
     * @return string
     */
    public function getAdvancedAuthPostfix(){
        return $this->zendConfig->search->auth->advancedPostfix;
    }

    /**
     * get mappings for the webpage
     *
     * @return array
     */
    public function getPageMappings() {
        if ($this->pageMappings == null) {
            $this->pageMappings = array();
            if ($this->zendConfig->params->client->mapping != null) {

                // get mapping config as iterable variable
                if ($this->zendConfig->params->client->mapping->from == null) {
                    $mapping = $this->zendConfig->params->client->mapping;
                } else {
                    $mapping = array($this->zendConfig->params->client->mapping);
                }

                //load mappings
                foreach($mapping AS $rule) {
                    $this->pageMappings[$rule->from] = $rule->to;
                }
            }
        }
        return $this->pageMappings;
    }

    /**
     * get mappings for the server request
     *
     * @return array
     */
    public function getServerMappings() {
        if ($this->serverMappings == null) {
            $this->serverMappings = array();
            if ($this->zendConfig->params->server->mapping != null) {

                // get mapping config as iterable variable
                if ($this->zendConfig->params->server->mapping->from == null) {
                    $mapping = $this->zendConfig->params->server->mapping;
                } else {
                    $mapping = array($this->zendConfig->params->server->mapping);
                }

                //load mappings
                foreach($mapping AS $rule) {
                    $this->serverMappings[$rule->from] = $rule->to;
                }
            }
        }
        return $this->serverMappings;
    }

    /**
     * get ignored parameters for the webpage request
     *
     * @return array with string as key and boolean true as value for each of them
     */
    public function getIgnoredPageParams() {
        if ($this->pageIgnores == null) {
            $this->pageIgnores = array();
            if (isset($this->zendConfig->params->client->ignore)) {

                // get ignore rules as iterable variable
                if($this->zendConfig->params->client->ignore->name == null) {
                    $ignoreRules = $this->zendConfig->params->client->ignore ;
                } else {
                    $ignoreRules = array($this->zendConfig->params->client->ignore );
                }

                // load ignore rules
                foreach($ignoreRules AS $i) {
                    $this->pageIgnores[$i->name] = true;
                }
            }

            $pageMappings = $this->getPageMappings();
            foreach ($pageMappings AS $from => $to) {
                $this->pageIgnores[$from] = true;
            }
        }
        return $this->pageIgnores;
    }

    /**
     * get ignored parameters for the server request
     *
     * @return array with string as key and boolean true as value for each of them
     */
    public function getIgnoredServerParams() {
        if ($this->serverIgnores == null) {
            $this->serverIgnores = array();
            if (isset($this->zendConfig->params->server->ignore)) {

                // get ignore rules as iterable variable
                if($this->zendConfig->params->server->ignore->name == null) {
                    $ignoreRules = $this->zendConfig->params->server->ignore ;
                } else {
                    $ignoreRules = array($this->zendConfig->params->server->ignore );
                }

                // load ignore rules
                foreach($ignoreRules AS $i) {
                    $this->serverIgnores[$i->name] = true;
                }
            }

            $serverMappings = $this->getServerMappings();
            foreach ($serverMappings AS $from => $to) {
                $this->serverIgnores[$from] = true;
            }
        }
        return $this->serverIgnores;
    }

    /**
     * get required parameters for the webpage request
     *
     * @return array string to string map (param-name as array-key; default value as array-value)
     */
    public function getRequiredPageParams(){
        if ($this->requiredPageParams == null) {
            $this->requiredPageParams = array();
            if ($this->zendConfig->params->client->required != null) {

                // get required params config as iterable variable
                if ($this->zendConfig->params->client->required->name == null) {
                    $requiredParams = $this->zendConfig->params->client->required;
                } else {
                    $requiredParams = array($this->zendConfig->params->client->required);
                }

                //load mappings
                foreach($requiredParams AS $param) {
                    $this->requiredPageParams[$param->name] = $param->default;
                }
            }
        }
        return $this->requiredPageParams;
    }

    /**
     * get required paramters for the server request
     *
     * @return array string to string map (param-name as array-key; default value as array-value)
     */
    function getRequiredServerParams(){
        if ($this->requiredServerParams == null) {
            $this->requiredServerParams = array();
            if ($this->zendConfig->params->server->required != null) {

                // get required params config as iterable variable
                if ($this->zendConfig->params->server->required->name == null) {
                    $requiredParams = $this->zendConfig->params->server->required;
                } else {
                    $requiredParams = array($this->zendConfig->params->server->required);
                }

                //load mappings
                foreach($requiredParams AS $param) {
                    $this->requiredServerParams[$param->name] = $param->default;
                }
            }
        }
        return $this->requiredServerParams;
    }

	/**
	 * get default connect timeout for all adapters
	 *
	 * @return string
	 **/
	 
	function getDefaultConnectTimeout() {
		return $this->zendConfig->search->timeouts->defaultConnectTimeout;
	}
	
	/**
	 * get default timeout for all adapters
	 *
	 * @return string
	 **/
	 
	function getDefaultTimeout() {
		return $this->zendConfig->search->timeouts->defaultTimeout;
	}
	
	/**
	 * get connect timeout for suggest adapter
	 *
	 * @return string
	 **/
	 
	function getSuggestConnectTimeout() {
		return $this->zendConfig->search->timeouts->suggestConnectTimeout;
	}
	
	/**
	 * get timeout for suggest adapter
	 *
	 * @return string
	 **/
	 
	function getSuggestTimeout() {
		return $this->zendConfig->search->timeouts->suggestTimeout;
	}
	
	/**
	 * get connect timeout for scic adapter
	 *
	 * @return string
	 **/
	 
	function getScicConnectTimeout() {
		return $this->zendConfig->search->timeouts->scicConnectTimeout;
	}
	
	/**
	 * get timeout for scic adapter
	 *
	 * @return string
	 **/
	 
	function getScicTimeout() {
		return $this->zendConfig->search->timeouts->scicTimeout;
	}
	
	/**
	 * get connect timeout for import adapter
	 *
	 * @return string
	 **/
	 
	function getImportConnectTimeout() {
		return $this->zendConfig->search->timeouts->importConnectTimeout;
	}
	
	/**
	 * get timeout for import adapter
	 *
	 * @return string
	 **/
	 
	function getImportTimeout() {
		return $this->zendConfig->search->timeouts->importTimeout;
	}

    /**
     * get encoding for the webpage
     *
     * @return string
     */
    function getPageContentEncoding() {
        return $this->zendConfig->encoding->pageContent;
    }

    /**
     * get encoding for the webpage url
     *
     * @return string
     */
    function getPageUrlEncoding() {
        return $this->zendConfig->encoding->pageURI;
    }

    /**
     * get server url encoding
     *
     * @return string
     */
    function getServerUrlEncoding() {
        return $this->zendConfig->encoding->serverURI;
    }
}