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
	private $data;
	private $previousType;
	private $previousParams;
	private $httpHeader = array();
	private $curlOptions = array();

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
	 * Set multiple options for a cURL transfer like described at {@link http://php.net/manual/en/function.curl-setopt.php}
	 *
	 * @link http://php.net/manual/en/function.curl-setopt.php
	 * @param array of options
	 * @return void
	 */
	public function setCurlOptions(array $options) {
		$this->curlOptions = $options;
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
		if ($this->data == null
				|| $this->type != $this->previousType
				|| count(array_diff_assoc($this->getParams(), $this->previousParams)) > 0
			) {
			$this->previousParams = $this->getParams();
			$this->previousType = $this->type;
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
		$params = $this->getParams();
		$config = $this->getConfig();
		if ($config->getLanguage() != '') {
			$this->addHttpHeaderFields(array('Accept-Language: ' . $config->getLanguage()));
		}

		$url = $this->getAuthenticationUrl();
		return $this->sendRequest($url);
	}

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
		curl_setopt_array($cResource, array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_MAXREDIRS => 2,
				CURLOPT_CONNECTTIMEOUT => 2,
				CURLOPT_TIMEOUT => 4,
				CURLOPT_USERAGENT => 'FACT-Finder PHP Framework V' . $this->getConfig()->getVersion(),
				CURLOPT_HTTPHEADER => $this->httpHeader
			)
		);
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
	private function getAdvancedAuthenticationUrl() {
		$config = $this->getConfig();
		$params = $this->getParams();
		
		if ($config->getChannel() != '') {
			$params['channel'] = $config->getChannel();
		}
			
		$ts         = time() . '000'; //millisecondes needed
		$prefix     = $config->getAdvancedAuthPrefix();
		$postfix    = $config->getAdvancedAuthPostfix();
		$authParams = "timestamp=$ts&username=".$config->getAuthUser()
		. '&password=' . md5($prefix . $ts . md5($config->getAuthPasswort()) . $postfix);
			
		$url = $config->getRequestProtokoll() . '://'
			. $config->getServerAddress() . ':' . $config->getServerPort() . '/'
			. $config->getContext() . '/'.$this->type.'?' . http_build_query($params, '', '&') . '&' . $authParams;
		return $url;
	}

	/**
	 * get url with simple authentication encryption
	 *
	 * @return string url
	 */
	private function getSimpleAuthenticationUrl() {
		$config = $this->getConfig();
		$params = $this->getParams();
		
		if ($config->getChannel() != '') {
			$params['channel'] = $config->getChannel();
		}
		
		$ts = time() . '000'; //millisecondes needed but won't be considered
		$authParams = "timestamp=$ts&username=".$config->getAuthUser()
			. '&password=' . md5($config->getAuthPasswort());
			
		$url = $config->getRequestProtokoll() . '://'
			. $config->getServerAddress() . ':' . $config->getServerPort() . '/'
			. $config->getContext() . '/'.$this->type.'?' . http_build_query($params, '', '&') . '&' . $authParams;
		return $url;
	}

	/**
	 * get url with http authentication
	 *
	 * @return string url
	 */
	private function getHttpAuthenticationUrl() {
		$config = $this->getConfig();
		$params = $this->getParams();
		
		if ($config->getChannel() != '') {
			$params['channel'] = $config->getChannel();
		}
		
		$auth = $config->getAuthUser() . ':' . $config->getAuthPasswort() . '@';
		if ($auth == ':@') $auth = '';
		
		$url = $config->getRequestProtokoll() . '://' . $auth
			. $config->getServerAddress() . ':' . $config->getServerPort() . '/'
			. $config->getContext() . '/'.$this->type.'?' . http_build_query($params, '', '&');
		return $url;
	}

	/**
	 * get url with no authentication
	 *
	 * @return string url
	 */
	public function getNonAuthenticationUrl() {
		$config = $this->getConfig();
		$params = $this->getParams();
		
		if ($config->getChannel() != '') {
			$params['channel'] = $config->getChannel();
		}
		
		$url = $config->getRequestProtokoll() . '://'
			. $config->getServerAddress() . ':' . $config->getServerPort() . '/'
			. $config->getContext() . '/'.$this->type.(count($params) ? '?' : '') . http_build_query($params, '', '&');
		
		return $url;
	}
}