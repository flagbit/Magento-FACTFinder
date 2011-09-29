<?php

/**
 * this data provider loads the data per http
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
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
     * please insure that this is the full action name, i.e. "Search.ff"
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
     * this implementation returns the data as string, no mather what content type set at the http response
     *
     * @return string data
     */
    public function getData()
    {
        if ($this->data == null || $this->getAuthenticationUrl() != $this->previousUrl) {
            $this->previousUrl = $this->getAuthenticationUrl();
            $this->data = $this->loadResponse();
        }
        return $this->data;
    }

    /**
     * this function sends the request to the server and loads the response data
     *
     * @throws Exception on connection error
     * @return response data
     **/
    protected function loadResponse()
    {
        if ($this->type == null) {
            throw new Exception('request type not set! can not do a request without knowing the type.');
        }

        $config = $this->getConfig();
        if ($config->getLanguage() != '') {
            $this->addHttpHeaderFields(array('Accept-Language: ' . $config->getLanguage()));
        }

        $url = $this->getAuthenticationUrl();
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
            throw new Exception("Connection failed. HTTP code: $httpCode", $httpCode);
        } else if ($httpCode == 0) {
            throw new Exception("Connection refused. $curlErr");
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

        if (empty($params['channel']) && $config->getChannel() != '') {
            $params['channel'] = $config->getChannel();
        }

        $ts         = time() . '000'; //millisecondes needed
        $prefix     = $config->getAdvancedAuthPrefix();
        $postfix    = $config->getAdvancedAuthPostfix();
        $authParams = "timestamp=$ts&username=".$config->getAuthUser()
        . '&password=' . md5($prefix . $ts . md5($config->getAuthPasswort()) . $postfix);

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/'.$this->type.'?' . http_build_query($params, '', '&')
            . (count($params)?'&':'') . $authParams;
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

        if ($config->getChannel() != '') {
            $params['channel'] = $config->getChannel();
        }

        $ts = time() . '000'; //millisecondes needed but won't be considered
        $authParams = "timestamp=$ts&username=".$config->getAuthUser()
            . '&password=' . md5($config->getAuthPasswort());

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/'.$this->type.'?' . http_build_query($params, '', '&')
            . (count($params)?'&':'') . $authParams;
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

        if ($config->getChannel() != '') {
            $params['channel'] = $config->getChannel();
        }

        $auth = $config->getAuthUser() . ':' . $config->getAuthPasswort() . '@';
        if ($auth == ':@') $auth = '';

        $url = $config->getRequestProtocol() . '://' . $auth
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/' . $this->type . (count($params)?'?':'')
            . http_build_query($params, '', '&');
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

        if ($config->getChannel() != '') {
            $params['channel'] = $config->getChannel();
        }

        $url = $config->getRequestProtocol() . '://'
            . $config->getServerAddress() . ':' . $config->getServerPort() . '/'
            . $config->getContext() . '/' . $this->type . (count($params)?'?':'')
            . http_build_query($params, '', '&');

        return $url;
    }
}