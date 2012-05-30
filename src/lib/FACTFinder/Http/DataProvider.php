<?php

/**
 * this data provider loads the data via http
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: DataProvider.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Http
 */
class FACTFinder_Http_DataProvider extends FACTFinder_Abstract_DataProvider
{
    protected $data;
    protected $previousUrl;
    protected $httpHeader = array();
    protected $curlOptions = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 4
            );

    /**
     * this implementation of the data provider uses the type as request path in addition to the request context path.
     * please ensure that this is the full action name, i.e. "Search.ff"
     *
     * @param string type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * set a option for a cURL request like described at {@link http://php.net/manual/en/function.curl-setopt.php}.
     * The second parameter can be set to false, so the option will not be overwritten if it already exists
     *
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @param the option key (should be a cURL constant)
     * @param the option value
     * @param boolean whether to overwrite existing options or not. optional, default = true
     * @return void
     */
    public function setCurlOption($option, $value, $overwriteExisting = true) {
        if ($overwriteExisting || !isset($this->curlOptions[$option])) {
            $this->curlOptions[$option] = $value;
        }
    }

    /**
     * Set multiple options for a cURL request like described at {@link http://php.net/manual/en/function.curl-setopt.php}
     *
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @param array of options
     * @return void
     */
    public function setCurlOptions(array $options) {
        foreach($options AS $option => $value) {
            $this->setCurlOption($option, $value);
        }
    }

    /**
     * add an array of HTTP header fields in the format  array('Content-type: text/plain', 'Content-length: 100')
     *
     * @param array $httpHeader
     * @return void
     */
    public function addHttpHeaderFields(array $httpHeader) {
        $this->httpHeader = array_merge($this->httpHeader, $httpHeader);
    }

    /**
     * {@inheritdoc}
     * this implementation returns the data as string, no matter what content type set at the http response
     *
     * @return string data
     */
    public function getData()
    {
        $url = $this->getAuthenticationUrl();
        if ($this->data == null || $url != $this->previousUrl) {
            $this->previousUrl = $url;
            $this->data = $this->loadResponse($url);
        }
        return $this->data;
    }

    /**
     * this function sends the request to the server and loads the response data
     *
     * @throws Exception on connection error
     * @return response data
     **/
    protected function loadResponse($url)
    {
        if ($this->type == null) {
            $this->log->error("Request type missing.");
            throw new Exception('request type not set! can not do a request without knowing the type.');
        }

        $config = $this->getConfig();
        if ($config->getLanguage() != '') {
            $this->addHttpHeaderFields(array('Accept-Language: ' . $config->getLanguage()));
        }

        if ($this->getConfig()->isDebugEnabled()) {
            $url .= '&verbose=true';
            if (isset($_SERVER['HTTP_REFERER'])) $this->setCurlOption(CURLOPT_REFERER, $_SERVER['HTTP_REFERER'], false);
        }
        return $this->sendRequest($url);
    }

    /**
     * this function returns the request url with the correct authentication method (set by the configuration).
     *
     * @return string url
     */
    public function getAuthenticationUrl() {
        $config = $this->getConfig();
        if ($config->isHttpAuthenticationType()) {
            $url = $this->getHttpAuthenticationUrl();
        } else if ($config->isSimpleAuthenticationType()) {
            $url = $this->getSimpleAuthenticationUrl();
        } else if ($config->isAdvancedAuthenticationType()) {
            $url = $this->getAdvancedAuthenticationUrl();
        } else {
            $url = $this->getNonAuthenticationUrl();
        }
        return $url;
    }

    /**
     * send request and return response data
     *
     * @param string url
     * @return string returned http body
     */
    protected function sendRequest($url)
    {
        $this->log->info("Trying to send request to ".$url."...");
        $cResource = curl_init($url);

        if (!empty($this->httpHeader)) {
            $this->curlOptions[CURLOPT_HTTPHEADER] = $this->httpHeader;
        }

        if (sizeof($this->curlOptions) > 0) {
            curl_setopt_array($cResource, $this->curlOptions);
        }

        $response = curl_exec($cResource);
        $httpCode = curl_getinfo($cResource, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($cResource);
        curl_close($cResource);

        if (intval($httpCode) >= 400) {
            $this->log->error("Conntection failed. HTTP code: $httpCode");
            throw new Exception("Connection failed. HTTP code: $httpCode", $httpCode);
        } else if ($httpCode == 0) {
            $this->log->error("Connection refused. $curlErr");
            throw new Exception("Connection refused. $curlErr");
        } else if (floor(intval($httpCode) / 100) == 2) { // all successful status codes (2**)
            $this->log->info("Received response from ".$url.".");
        }        
        
        return $response;
    }

    /**
     * get url with advanced authentication encryption
     *
     * @return string url
     */
    protected function getAdvancedAuthenticationUrl() {
        $config = $this->getConfig();
        $params = $this->getParams();        
        $this->log->info("Server Request Params: ".http_build_query($params, '', ', '));

        $params['channel'] = $this->getChannel($params, $config);

        $ts         = time() . '000'; //millisecondes needed
        $prefix     = $config->getAdvancedAuthPrefix();
        $postfix    = $config->getAdvancedAuthPostfix();
        $authParams = "timestamp=$ts&username=".$config->getAuthUser()
        . '&password=' . md5($prefix . $ts . md5($config->getAuthPasswort()) . $postfix);

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/'.$this->type.'?' . http_build_query($params, '', '&')
            . (count($params)?'&':'') . $authParams;
            
        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with simple authentication encryption
     *
     * @return string url
     */
    protected function getSimpleAuthenticationUrl() {
        $config = $this->getConfig();
        $params = $this->getParams();        
        $this->log->info("Server Request Params: ".http_build_query($params, '', ', '));

        $params['channel'] = $this->getChannel($params, $config);

        $ts = time() . '000'; //millisecondes needed but won't be considered
        $authParams = "timestamp=$ts&username=".$config->getAuthUser()
            . '&password=' . md5($config->getAuthPasswort());

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/'.$this->type.'?' . http_build_query($params, '', '&')
            . (count($params)?'&':'') . $authParams;
        
        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with http authentication
     *
     * @return string url
     */
    protected function getHttpAuthenticationUrl() {
        $config = $this->getConfig();
        $params = $this->getParams();        
        $this->log->info("Server Request Params: ".http_build_query($params, '', ', '));

        $params['channel'] = $this->getChannel($params, $config);

        $auth = $config->getAuthUser() . ':' . $config->getAuthPasswort() . '@';
        if ($auth == ':@') $auth = '';

        $url = $config->getRequestProtocol() . '://' . $auth
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/' . $this->type . (count($params)?'?':'')
            . http_build_query($params, '', '&');
            
        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get url with no authentication.
     *
     * @return string url
     */
    public function getNonAuthenticationUrl() {
        $config = $this->getConfig();
        $params = $this->getParams();        
        $this->log->info("Server Request Params: ".http_build_query($params, '', ', '));

        $params['channel'] = $this->getChannel($params, $config);

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/' . $this->type . (count($params)?'?':'')
            . http_build_query($params, '', '&');

        // The following line removes all []-indices from array parameters, because tomcat doesn't need them
        $url = preg_replace("/%5B[A-Za-z0-9]*%5D/", "", $url);
        $this->log->info("Request Url: ".$url);
        return $url;
    }

    /**
     * get channel from params or config (params override config)
     *
     * @param array parameterse
     * @FACTFinderAbstractConfiguration config
     * @return string channel
     */
    protected function getChannel($params, $config) {
        $channel = '';
        if (!empty($params['channel'])) {
            $channel = $params['channel'];
        } else if($config->getChannel() != '') {
            $channel = $config->getChannel();
        }
        return $channel;
    }
}