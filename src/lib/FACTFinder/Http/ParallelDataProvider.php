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
    /**
     * @var FACTFinder_Http_ParallelDataProvider
     */
    protected static $instance;

    /**
     * @var array of FACTFinder_Http_DataProviderProxy
     */
    protected static $dataProviders = array();
	
    protected $data = array();
    protected $httpCodes = array();
    protected $curlErrnos = array();
    protected $curlErrors = array();
	
	/**
	 * singleton
	 */
	private function __construct() {}
	
	/**
	 * @return FACTFinder_Abstract_DataProvider
	 */
	public static function getDataProvider(array $params = null, FACTFinder_Abstract_Configuration $config = null, $log = null) {
		if (self::$instance == null) {
			self::$instance = new FACTFinder_Http_ParallelDataProvider();
		}
		$id = 'proxy' . count(self::$dataProviders); // use prefix so the id is a string
		self::$dataProviders[$id] = new FACTFinder_Http_DataProviderProxy($params, $config, $log);
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
		self::executeHandles($multiHandle, $handles);
    }

	protected static function initHandles($multiHandle) {
		$handles = array();
		foreach(self::$dataProviders AS $id => $dataProvider) {
            /**
             * @var $dataProvider FACTFinder_Http_DataProviderProxy
             */
            if(!$dataProvider->hasUrlChanged())
			{
				$handles[$id] = null;
				continue;
			}
            try
            {
                $dataProvider->prepareRequest();
            }
            catch (NoRequestTypeException $e)
            {
                $handles[$id] = null;
                continue;
            }

			$handle = curl_init();
			curl_setopt_array($handle, $dataProvider->getCurlOptions());
			
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
			if (curl_multi_select($multiHandle) != -1) usleep(100); // unsatisfactory workaround for bug https://bugs.php.net/bug.php?id=63842
            do {
                $mrc = curl_multi_exec($multiHandle, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
		
		// TODO: read data which is already loaded while the other data is still loading (is this possible?)
		// TODO: handle errors

		//close the handles
		$data = array();
        $httpCodes = array();
        $curlErrnos = array();
        $curlErrors = array();
		foreach($handles AS $id => $handle) {
			if($handle == null)
			{
				$data[$id] = null;
                $httpCodes[$id] = null;
                $curlErrnos[$id] = null;
                $curlErrors[$id] = null;
				continue;
			}
			$data[$id] = curl_multi_getcontent($handle);
            $httpCodes[$id] = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $curlErrnos[$id] = curl_errno($handle);
            $curlErrors[$id] = curl_error($handle);

			curl_multi_remove_handle($multiHandle, $handle);
            curl_close($handle);
		}
		curl_multi_close($multiHandle);

        self::$instance->setData($data);
        self::$instance->setHttpCodes($httpCodes);
        self::$instance->setCurlErrnos($curlErrnos);
        self::$instance->setCurlErrors($curlErrors);
	}

	protected function setData(array $data) {
		foreach($data as $id => $dataItem)
		{
			if($dataItem == null)
				continue;
			$this->data[$id] = $dataItem;
		}		
	}

    protected function setHttpCodes(array $httpCodes) {
        foreach($httpCodes as $id => $httpCode)
        {
            if($httpCode == null)
                continue;
            $this->httpCodes[$id] = $httpCode;
        }
    }

    protected function setCurlErrnos(array $curlErrnos) {
        foreach($curlErrnos as $id => $curlErrno)
        {
            if($curlErrno == null)
                continue;
            $this->curlErrnos[$id] = $curlErrno;
        }
    }

    protected function setCurlErrors(array $curlErrors) {
        foreach($curlErrors as $id => $curlError)
        {
            if($curlError == null)
                continue;
            $this->curlErrors[$id] = $curlError;
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

    public function getLastHttpCode($id)
    {
        if (self::$dataProviders[$id]->hasUrlChanged()) {
            throw new DataNotLoadedException("Implementation Error: the data is not up to date. Please use 'FACTFinder_Http_ParallelDataProvider::loadAllData' before trying to get data!");
        }
        return isset($this->httpCodes[$id]) ? $this->httpCodes[$id] : null;
    }

    public function getLastCurlErrno($id)
    {
        if (self::$dataProviders[$id]->hasUrlChanged()) {
            throw new DataNotLoadedException("Implementation Error: the data is not up to date. Please use 'FACTFinder_Http_ParallelDataProvider::loadAllData' before trying to get data!");
        }
        return isset($this->curlErrnos[$id]) ? $this->curlErrnos[$id] : null;
    }

    public function getLastCurlError($id)
    {
        if (self::$dataProviders[$id]->hasUrlChanged()) {
            throw new DataNotLoadedException("Implementation Error: the data is not up to date. Please use 'FACTFinder_Http_ParallelDataProvider::loadAllData' before trying to get data!");
        }
        return isset($this->curlErrors[$id]) ? $this->curlErrors[$id] : null;
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

    /**
     * @var FACTFinder_Http_ParallelDataProvider
     */
    private $master;
	
	public function register($id, FACTFinder_Http_ParallelDataProvider $master) {
		$this->id = $id;
		$this->master = $master;
	}
	
	public function getData() {
		return $this->master->getData($this->id);
	}

    public function getLastHttpCode() {
        return $this->master->getLastHttpCode($this->id);
    }

    public function getLastCurlErrno() {
        return $this->master->getLastCurlErrno($this->id);
    }

    public function getLastCurlError() {
        return $this->master->getLastCurlError($this->id);
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