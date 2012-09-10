<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Http
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this data provider has the ability to make multiple http request at the same time
 *
 * @todo      describe usage
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: DataProvider.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Http
 */
class FACTFinder_Http_ParallelDataProvider
{
	protected static $instance;
	protected static $dataProviders = array();
	
    protected $data;
	
	/**
	 * singleton
	 */
	private function __construct() {}
	
	/**
	 * @return FACTFinder_Abstract_DataProvider
	 */
	public static function getDataProvider(array $params = null, FACTFinder_Abstract_Configuration $config = null) {
		if (self::$instance == null) {
			self::$instance = new FACTFinder_Http_ParallelDataProvider();
		}
		$id = 'proxy' . count(self::$dataProviders); // use prefix so the id is a string
		self::$dataProviders[$id] = new FACTFinder_Http_DataProviderProxy($params, $config);
		self::$dataProviders[$id]->register($id, self::$instance);
		
		return self::$dataProviders[$id];
	}

    /**
     * this function sends all request to the server and loads the response data
	 *
	 * @return void
     **/
    public static function loadAllData()
    {
		if (self::$instance == null || count(self::$dataProviders) == 0) {
			return;
		}
		
		// TODO: optimize:
			// - warn if several loadings were done
			// - add logging
	
		// init handles
		$multiHandle = curl_multi_init();
        $handles = self::initHandles($multiHandle);
		$data = self::executeHandles($multiHandle, $handles);
		
		self::$instance->setData($data);
    }

	protected static function initHandles($multiHandle) {
		$handles = array();
		foreach(self::$dataProviders AS $id => $dataProvider) {
			if(!$dataProvider->hasUrlChanged())
			{
				$handles[$id] = null;
				continue;
			}
			$dataProvider->setPreviousUrl($dataProvider->getNonAuthenticationUrl());
			$handle = curl_init($dataProvider->getAuthenticationUrl());
			
			$curlOptions = $dataProvider->getCurlOptions();
			$curlOptions[CURLOPT_HTTPHEADER] = $dataProvider->getHttpHeader();
			$curlOptions[CURLOPT_RETURNTRANSFER] = true; // this is a must have option, so the data can be saved
			curl_setopt_array($handle, $curlOptions);
			
			$handles[$id] = $handle;
			curl_multi_add_handle($multiHandle,$handle);
		}
		return $handles;
	}
	
	protected static function executeHandles($multiHandle, $handles) {
		//execute the handles
		$active = null;
		do {
			$mrc = curl_multi_exec($multiHandle, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($multiHandle) != -1) {
				do {
					$mrc = curl_multi_exec($multiHandle, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		
		// TODO: read data which is already loaded while the other data is still loading (is this possible?)
		// TODO: handle errors

		//close the handles
		$data = array();
		foreach($handles AS $id => $handle) {
			if($handle == null)
			{
				$data[$id] = null;
				continue;
			}
			$data[$id] = curl_multi_getcontent($handle);
			curl_multi_remove_handle($multiHandle, $handle);
		}
		curl_multi_close($multiHandle);
		return $data;
	}
	
	/**
	 * 
	 * internal method to apply data to 
	 */
	protected function setData(array $data) {
		foreach($data as $id => $dataItem)
		{
			if($dataItem == null)
				continue;
			$this->data[$id] = $data[$id];
		}		
	}
	
    /**
	 * this method is called by the proxy data providers on the one and only existing instance
	 *
     * @return string data
     */
    public function getData($id)
    {
		if (self::$dataProviders[$id]->hasUrlChanged()) {
           throw new DataNotLoadedException("Implementation Error: the data is not up to date. Please use 'FACTFinder_Http_ParallelDataProvider::loadAllData' before trying to get data!");
        }
        return isset($this->data[$id]) ? $this->data[$id] : null;
    }
}

/**
 * @internal
 * this is an anonymous inner class which can't be used successfully from the outside
 *
 * @package   FACTFinder\Http
 */
class FACTFinder_Http_DataProviderProxy extends FACTFinder_Http_DataProvider
{
	private $id;
	private $master;
	
	public function register($id, FACTFinder_Http_ParallelDataProvider $master) {
		$this->id = $id;
		$this->master = $master;
	}
	
	public function getData() {
		return $this->master->getData($this->id);
	}
	
	public function getCurlOptions() {
		return $this->curlOptions;
	}
	
	public function getHttpHeader() {
		return $this->httpHeader;
	}
}

/**
 * @internal
 * Exception type needed for parallel data provider
 *
 * @package   FACTFinder\Http
 */
class DataNotLoadedException extends Exception {}