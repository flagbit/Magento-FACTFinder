<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Http
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this data provider loads the data via http
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: DataProvider.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Http
 */

class FACTFinder_Http_DataProvider extends FACTFinder_Abstract_DataProvider
{
    /**
     * @var FACTFinder_CurlInterface
     */
    protected $curl;

    protected $data;

    /**
     * @var FACTFinder_Http_UrlBuilder
     */
    protected $urlBuilder;
    protected $previousUrl = '';
    protected $httpHeader = array();
    protected $curlOptions = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            );
    protected $lastHttpCode = null;
    protected $lastCurlErrno = null;
    protected $lastCurlError = null;

	function __construct(array $params = null, FACTFinder_Abstract_Configuration $config = null, FACTFinder_Abstract_Logger $log = null, FACTFinder_CurlInterface $curl = null) {
        if($curl === null)
        {
            $curl = FF::getInstance('curl');
        }
        $this->curl = $curl;
        $this->urlBuilder = FF::getInstance('http/urlBuilder', $params, $config, $log);
		parent::__construct($params, $config, $log);
		$this->setCurlOptions(array(
            CURLOPT_CONNECTTIMEOUT => $this->getConfig()->getDefaultConnectTimeout(),
            CURLOPT_TIMEOUT => $this->getConfig()->getDefaultTimeout()
        ));
	}

    /**
     * sets factfinder params object
     *
     * @param array params
     * @return void
     **/
    public function setParams(array $params)
    {
        $this->urlBuilder->setParams($params);
    }

    /**
     * set single param
     *
     * @param string name
     * @param string value
     * @return void
     **/
    public function setParam($name, $value)
    {
        $this->urlBuilder->setParam($name, $value);
    }

    /**
     * unset single param
     *
     * @param string name
     * @return void
     **/
    public function unsetParam($name)
    {
        $this->urlBuilder->unsetParam($name);
    }

    /**
     * set single param with multiple values
     *
     * @param string name
     * @param array of strings values
     * @return void
     **/
    public function setArrayParam($name, $values)
    {
        $this->urlBuilder->setArrayParam($name, $values);
    }
			
    /**
     * this implementation of the data provider uses the type as request path in addition to the request context path.
     * please ensure that this is the full action name, i.e. "Search.ff"
     *
     * @param string type
     */
    public function setType($type)
    {
        $this->urlBuilder->setAction($type);
    }

    protected function getType()
    {
        return $this->urlBuilder->getAction();
    }

    /**
     * @return array
     **/
    public function getParams()
    {
        return $this->urlBuilder->getParams();
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
     * this implementation returns the data as string, no matter what content type set at the http response
     *
     * @return string data
     */
    public function getData()
    {
		if ($this->hasUrlChanged()) {
            $this->data = $this->loadResponse();
        }
        return $this->data;
    }
	
	/**
	 * sets the URL that was used for the most recent request
	 **/
	public function setPreviousUrl($url)
	{
		$this->previousUrl = $url;
	}
	
	/**
	 * checks whether the URL (and thus the parameters) have changed since last loading the data
	 **/
	public function hasUrlChanged()
	{
		return $this->urlBuilder->getNonAuthenticationUrl() != $this->previousUrl;
	}

    /**
     * this function sends the request to the server and loads the response data
     *
     * @throws Exception on connection error
     * @return response data
     **/
    protected function loadResponse()
    {
        try
        {
            $this->prepareRequest();
        }
        catch(NoRequestTypeException $e)
        {
            return "";
        }

        $cResource = $this->curl->curl_init();

        if (sizeof($this->curlOptions) > 0) {
            $this->curl->curl_setopt_array($cResource, $this->curlOptions);
        }

        $response = $this->curl->curl_exec($cResource);
        $this->lastHttpCode = $this->curl->curl_getinfo($cResource, CURLINFO_HTTP_CODE);
        $this->lastCurlErrno = $this->curl->curl_errno($cResource);
        $this->lastCurlError = $this->curl->curl_error($cResource);
        $this->curl->curl_close($cResource);

        if (intval($this->lastHttpCode) >= 400) {
            $this->log->error("Connection failed. HTTP code: $this->lastHttpCode");
        } else if ($this->lastHttpCode == 0) {
            $this->log->error("Connection refused. $this->lastCurlError");
        } else if (floor(intval($this->lastHttpCode) / 100) == 2) { // all successful status codes (2**)
            $this->log->info("Received response!");
        }

        return $response;
    }

    /**
     * Sets up curl all necessary cURL options (including URL!)
     *
     * @throws Exception
     */
    public function prepareRequest()
    {
        if ($this->getType() === null) {
            $this->log->debug("Request type missing.");
            throw new NoRequestTypeException('Request type was not set! Cannot send request without knowing the type.');
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

        if (!empty($this->httpHeader)) {
            $this->setCurlOption(CURLOPT_HTTPHEADER, $this->httpHeader);
        }

        $this->setCurlOption(CURLOPT_URL, $url);

        $this->setPreviousUrl($this->urlBuilder->getNonAuthenticationUrl());
        $this->log->info("Trying to send request to " . $url . "...");
    }

    /**
     * this function returns the request url with the correct authentication method (set by the configuration).
     *
     * @return string url
     */
    public function getAuthenticationUrl() {
        $config = $this->getConfig();
        if ($config->isHttpAuthenticationType()) {
            $url = $this->urlBuilder->getHttpAuthenticationUrl();
        } else if ($config->isSimpleAuthenticationType()) {
            $url = $this->urlBuilder->getSimpleAuthenticationUrl();
        } else if ($config->isAdvancedAuthenticationType()) {
            $url = $this->urlBuilder->getAdvancedAuthenticationUrl();
        } else {
            $url = $this->urlBuilder->getNonAuthenticationUrl();
        }
        return $url;
    }

    public function getNonAuthenticationUrl()
    {
        return $this->urlBuilder->getNonAuthenticationUrl();
    }

    public function getLastHttpCode()
    {
        if($this->lastHttpCode === null)
            throw new Exception("Cannot return last HTTP code. No request has been sent.");

        return $this->lastHttpCode;
    }

    public function getLastCurlError()
    {
        if($this->lastCurlError === null)
            throw new Exception("Cannot return last curl error. No request has been sent.");

        return $this->lastCurlError;
    }

    public function getLastCurlErrno()
    {
        if($this->lastCurlErrno === null)
            throw new Exception("Cannot return last curl errno. No request has been sent.");

        return $this->lastCurlErrno;
    }
}

/**
 * @internal
 * Exception type needed for data provider
 *
 * @package   FACTFinder\Http
 */
class NoRequestTypeException extends Exception {}