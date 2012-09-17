<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

require_once BP.DS.'lib'.DS.'FACTFinder'.DS.'Loader.php';

/**
 * Model class
 *
 * Facade that hides FACT-Finder API Framework for Magento
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: Facade.php 906 2011-09-30 14:10:05Z tuerk $
 */
class Flagbit_FactFinder_Model_Facade
{
    /**
     * Two-dimensional array of FACT-Finder adapters
     * First-dimension index corresponds to type
     * Second-dimension index corresponds to channel
     * @var array of FACTFinder_Abstract_Adapter
     */
    protected $_adapters = array();

    /**
     * FACT-Finder search adapter
     * @var FACTFinder_Abstract_SearchAdapter
     */
    protected $_searchAdapter = null;

    /**
     * @var FACTFinder_Abstract_Configuration
     */
    protected $_config = null;

    /**
     * @var FACTFinder_ParametersParser
     */
    protected $_paramsParser = null;

    /**
     * @var FACTFinder_Abstract_DataProvider
     */
    protected $_dataProvider = null;

    /**
     * @var array
     */
    protected $_afterSearchNavigation = null;

    /**
     * @var array
     */
    protected $_searchResultProductIds = null;

	/**
     * @var array
     */
    protected $_currentFactfinderCategoryPath = null;
    
	/**
	 * logger object to log all module internals
	 * @var FACTFinder_Abstract_Logger
	 */
	protected $_logger = null;
	
	public function __construct($arg = null)
    {
        if ($arg != null && $arg instanceof FACTFinder_Abstract_Logger) {
            FF::setLogger($arg);
			$this->_logger = $arg;
        } else {
            $logger = Mage::helper('factfinder/debug');
            FF::setLogger($logger);
			$this->_logger = $logger;
        }
    }

    /**
     * Used to allow and delegate generic methods. Valid signatures:
     * get_____Adapter($channel = null)
     * configure____Adapter($params = array(), $channel = null)
     *
     * @param string $function
     * @param array $arguments
     * @return FACTFinder_Abstract_Adapter|null
     * @throws Exception
     */
    public function __call($function, $arguments)
    {
        $matches = array();
        if (preg_match('/^get(.+)Adapter$/', $function, $matches))
        {
            // We have a get______Adapter($channel) method!
            // The first argument (if any) will be treated as a channel


            $type = $matches[1];
            $type{0} = strtolower($type{0});

            $format = $this->_getFormat($type);

            $channel = null;
            if(count($arguments))
                $channel = $arguments[0];

            return $this->_getAdapter($format, $type, $channel);
        }
        elseif (preg_match('/^configure(.+)Adapter$/', $function, $matches))
        {
            // We have a configure_____Adapter($params, $channel) method!
            // The first argument (if any) will be treated as an array of params as key-value pairs
            // The second argument (if any) will be treated as a channel

            $type = $matches[1];
            $type{0} = strtolower($type{0});

            $format = $this->_getFormat($type);

            $channel = null;
            if(count($arguments) > 1)
                $channel = $arguments[1];

            $adapter = $this->_getAdapter($format, $type, $channel);

            if(count($arguments))
            {
                foreach($arguments[0] as $key => $value)
                    $adapter->setParam($key, $value);
            }

            return null;
        }
        else
        {
            throw new Exception("Call to undefined method ".$function."() in file ".__FILE__." on line ".__LINE__);
        }
    }

    public function getRequestParams()
    {
        return $this->_getParamsParser()->getRequestParams();
    }

    /**
     * @return FACTFinder_ParametersParser
     */
    protected function _getParamsParser()
    {
        if ($this->_paramsParser == null) {
            $config = $this->_getConfiguration();
            $encodingHandler = FF::getSingleton('encodingHandler', $config);
            $this->_paramsParser = FF::getInstance('parametersParser', $config, $encodingHandler);
        }
        return $this->_paramsParser;
    }
	
	// This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function _________Configuration_Handling__________() { }

    /**
     * @param array $configArray
     */
    public function setConfiguration($configArray)
    {
        $this->_config = FF::getSingleton('configuration', $configArray);
    }

    /**
     * @param $configArray
     * @return FACTFinderCustom_Configuration config
     */
    protected function _getConfiguration($configArray = null)
    {
        if ($this->_config == null) {
            $this->_config = FF::getSingleton('configuration', $configArray);
        }
        return $this->_config;
    }

    /**
     * @param int $storeId
     * @return \Flagbit_FactFinder_Model_Facade
     */
    public function setStoreId($storeId) {
        $this->_getConfiguration()->setStoreId($storeId);

        return $this;
    }
	
	
	
	
	
	
	
	
	
	
	// This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function _________Data_Provider_Handling__________() { }
	
	/**
	 * @param FACTFinder_Abstract_DataProvider
	 **/
	protected function _setGlobalDataProvider($dataProvider)
	{
		$this->_dataProvider = $dataProvider;
	}

    /**
     * @return FACTFinder_Abstract_DataProvider
     */
    protected function _getGlobalDataProvider()
    {
        if ($this->_dataProvider == null) {
            $config = $this->_getConfiguration();
            $params = $this->_getParamsParser()->getServerRequestParams();

            $this->_setGlobalDataProvider(FF::getInstance('http/dataProvider', $params, $config));
        }
        return $this->_dataProvider;
    }
	
	protected function _globalDataProviderExists()
	{
		return is_subclass_of($this->_dataProvider, 'FACTFinder_Abstract_DataProvider');
	}
	
	/**
	 * @return FACTFinder_Abstract_DataProvider
	 **/
	protected function _getParallelDataProvider()
	{
		$config = $this->_getConfiguration();
		$params = $this->_getParamsParser()->getServerRequestParams();
		
		$dp = FACTFinder_Http_ParallelDataProvider::getDataProvider($params, $config);
				
		return $dp;
	}

    /**
     * set single parameter, which will be looped through to the FACT-Finder request
     *
     * @param string $name
     * @param string $value
     * @param bool $log
     * @param null|FACTFinder_Abstract_DataProvider $dataProvider
     * @return \Flagbit_FactFinder_Model_Facade
     */
    protected function _setParam($name, $value, $log = true, $dataProvider = null)
    {
        if($log){
            Mage::helper('factfinder/debug')->log('set Param:'.$name.' => '.$value);
        }
		if($dataProvider == null)
			$this->_getGlobalDataProvider()->setParam($name, $value);
		else
			$dataProvider->setParam($name, $value);
        return $this;
    }

    protected function _loadAllData()
    {
        FACTFinder_Http_ParallelDataProvider::loadAllData();
    }
	
	
	
	
	
	
    // This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function ___________FF_Adapter_Getters___________() { }


    /**
     * @param $type
     * @return string
     */
    protected function _getFormat($type)
    {
        $format = 'http';
        if ($type != 'scic' && $type != 'suggest') {
            $version = $this->_getConfiguration()->getFactFinderVersion();
            $format = 'xml' . $version;
            return $format;
        }
        return $format;
    }

    /**
     * @param string $format
     * @param string $type
     * @param null|string $channel
     * @return FACTFinder_Abstract_Adapter
     */
    public function _getAdapter($format, $type, $channel = null)
    {
        if(!$channel)
            $channel = $this->_getConfiguration()->getChannel();

        if(!isset($this->_adapters[$type][$channel]))
        {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $dataProvider      = $this->_getParallelDataProvider();

            $dataProvider->setParam('channel', $channel);

            $this->_adapters[$type][$channel] = FF::getInstance(
                $format.'/'.$type.'Adapter',
                $dataProvider,
                $this->_getParamsParser(),
                $encodingHandler,
                $this->_logger
            );
        }
        return $this->_adapters[$type][$channel];
    }

    /**
     * @param bool $collectParams
     * @return FACTFinder_Abstract_SearchAdapter
     */
    protected function _getSearchAdapter()
    {
        if ($this->_searchAdapter == null)
		{
			$this->_loadSearchAdapter();
        }

        return $this->_searchAdapter;
    }

	protected function _loadSearchAdapter($parallel = false)
	{
		$config					= $this->_getConfiguration();
		$encodingHandler		= FF::getSingleton('encodingHandler', $config);
		if(!$parallel)
		{
			$dataProvider			= $this->_getGlobalDataProvider();
		}
		else
		{
			$dataProvider			= $this->_getParallelDataProvider();
			if($this->_globalDataProviderExists())
				$dataProvider->setParams($this->_getGlobalDataProvider()->getParams());
			$this->_setGlobalDataProvider($dataProvider);
		}
		
		$this->_searchAdapter	= FF::getSingleton(
			'xml'.$this->_getConfiguration()->getFactFinderVersion().'/searchAdapter',
			$dataProvider,
			$this->_getParamsParser(),
			$encodingHandler
		);
	}

    /**
     * @return string
     */
    public function getAuthenticationUrl()
    {
        $dataProvider = $this->_getGlobalDataProvider();
        $dataProvider->setType('Management.ff');
        return $dataProvider->getNonAuthenticationUrl();
    }

    /**
     * @return string
     */
    public function getSuggestUrl()
    {
        $dataProvider = $this->_getGlobalDataProvider();
        $dataProvider->setType('Suggest.ff');
        $dataProvider->setParams(array());

        return $dataProvider->getNonAuthenticationUrl();
    }
	
	// This is not a function!
	// It's actually a headline for Notepad++'s Function List plug-in.
	// And yes, I feel bad about it.
	private function ___________FF_Object_Getters____________() { }

    public function applyTracking($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getScicAdapter($channel)->applyTracking();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getAfterSearchNavigation($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSearchAdapter($channel)->getAsn();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getCampaigns($channel = null)
    {
        try {
			$this->_loadAllData();
            return $this->getSearchAdapter($channel)->getCampaigns();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getProductCampaigns($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getProductCampaignAdapter($channel)->getCampaigns();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getRecommendations($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getRecommendationAdapter($channel)->getRecommendations();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getSearchError($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSearchAdapter($channel)->getError();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getSearchFilters($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSearchAdapter($channel)->getSearchParams()->getFilters();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getSearchResult($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSearchAdapter($channel)->getResult();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getSearchResultCount($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSearchAdapter($channel)->getResult()->getFoundRecordsCount();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    /**
     * execute search
     */
    public function checkStatus($configArray = null)
    {
        $status = false;
        try {
            $this->_getConfiguration($configArray);
			
			
            $this->_setParam('query', 'FACT-Finder Version');
            $this->_setParam('productsPerPage', '1');
			
			$searchAdapter = $this->_getSearchAdapter();
            $this->_loadAllData();
			$status = $searchAdapter->getStatus() == 'resultsFound';
        } catch (Exception $e) {
            $status = false;
        }
        return $status;
    }

    /**
     * @param string $channel
     * @return string
     */
    public function getSuggestions($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSuggestAdapter($channel)->getSuggestions();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    /**
     * @param string $channel
     * @return array
     */
    public function getTagCloud($channel = null)
    {
		try {
            $this->_loadAllData();
			return $this->getTagCloudAdapter($channel)->getTagCloud();
		} catch (Exception $e) {
            Mage::logException($e);
			return null;
        }
    }
}
