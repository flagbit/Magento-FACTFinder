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
     * First-dimension key corresponds to type
     * Second-dimension key corresponds to channel
     * @var array of FACTFinder_Abstract_Adapter
     */
    protected $_adapters = array();

    /**
     * Key corresponds to channel
     * @var array of FACTFinder_Http_StatusHelper
     */
    protected $_statusHelpers = array();

    /**
     * @var FACTFinder_Abstract_Configuration
     */
    protected $_config = null;

    /**
     * @var FACTFinder_ParametersParser
     */
    protected $_paramsParser = null;

    /**
     * @var FACTFinder_Http_UrlBuilder
     */
    protected $_urlBuilder = null;

	/**
	 * logger object to log all module internals
	 * @var FACTFinder_Abstract_Logger
	 */
	protected $_logger = null;
	
	public function __construct($arg = null)
    {
        if ($arg === null || !($arg instanceof FACTFinder_Abstract_Logger)) {
            $arg = Mage::helper('factfinder/debug');
        }
        FF::setLogger($arg);
        $this->_logger = $arg;
    }
    
    /**
     * Used to allow and delegate generic methods.
     * Valid signatures:
     *
     * @method FACTFinder_Abstract_<$type>Adapter get<$type>Adapter($channel = null, $id = null)
     * get adapter class from the factfinder library; this is not recommended, but some cases this might be necessary.
     *
     * @method null configure<$type>Adapter(array $params, $channel = null, $id = null)
     * @param array $params the parameters which should be set 
     * set parameters for the specified adapter
     *
     * this applies to both kind of methods:
     * - the (correct) implementation of the specified adapter will be determined automatically, depending on configuration and which adapters acutally exit
     * - $type will be fetched out of the method name, so for example for the call "getSearchAdapter" $type is "search"
     * - this are the two last (optional) accepted parameters:
     * @param string $channel the factfinder channel which should be requested by this adapter. if channel is null, the primary channel from the configuration is used [default: null]
     * @param string $id optional id to enable multiple adapters of the same type and for the same channel [default: null]
     *
     * @param string $function
     * @param array $arguments
     * @return FACTFinder_Abstract_Adapter|null depending on which type is called
     * @throws Exception if such an adapter does not exist or an non-existing method is called
     */
    public function __call($function, $arguments)
    {
        $matches = array();
        $configureAdapter = false;
        $channelArgPos = 0;
        if (preg_match('/^get(.+)Adapter$/', $function, $matches))
        {
            // We have a get______Adapter($channel = null, $id = null) method!
            $channelArgPos = 0; // The first argument (if any) will be treated as a channel
            $idArgPos = 1; // The second argument (if any) will be treated as $id
        }
        elseif (preg_match('/^configure(.+)Adapter$/', $function, $matches))
        {
            // We have a configure_____Adapter(array $params, $channel = null, $id = null) method!
            $configureAdapter = true;
            // The first argument (if any) will be treated as an array of params as key-value pairs
            $channelArgPos = 1; // The second argument (if any) will be treated as a channel
            $idArgPos = 2; // The third argument (if any) will be treated as $id
        }
        else
        {
            throw new Exception("Call to undefined method ".$function."() in file ".__FILE__." on line ".__LINE__);
        }

        $type = $matches[1];
        $type[0] = strtolower($type[0]);

        $format = $this->_getFormat($type);

        $channel = null;
        if(count($arguments) > $channelArgPos)
            $channel = $arguments[$channelArgPos];

        $id = null;
        if(count($arguments) > $idArgPos)
            $id = $arguments[$idArgPos];
            
        $adapter = $this->_getAdapter($format, $type, $channel, $id);

        if($configureAdapter && count($arguments))
        {
            foreach($arguments[0] as $key => $value)
                $adapter->setParam($key, $value);

            return null;
        }
        else
        {
            return $adapter;
        }
    }

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
     * @return FACTFinder_Abstract_Adapter
     */
    protected function _getAdapter($format, $type, $channel = null, $id = null)
    {
        if(!$id)
            $id = '';
        if(!$channel)
            $channel = $this->_getConfiguration()->getChannel();

        $hashKey = $type.$id;
        if(!isset($this->_adapters[$hashKey][$channel]))
        {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $dataProvider      = $this->_getParallelDataProvider();
            $dataProvider->setParam('channel', $channel);
            $this->_adapters[$hashKey][$channel] = FF::getInstance(
                $format.'/'.$type.'Adapter',
                $dataProvider,
                $this->_getParamsParser(),
                $encodingHandler,
                $this->_logger
            );
        }
        return $this->_adapters[$hashKey][$channel];
    }

    public function configureStatusHelper($channel = null)
    {
        if(!$channel)
            $channel = $this->_getConfiguration()->getChannel();
        if(!isset($this->_statusHelpers[$channel]))
        {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $this->_statusHelpers[$channel] = FF::getInstance(
                'http/statusHelper',
                $config,
                $this->_logger,
                $channel
            );
        }
    }

    /**
     * @return FACTFinderCustom_Configuration config
     */
    protected function _getConfiguration($configArray = null)
    {
        if ($this->_config == null) {
            $this->_config = FF::getSingleton('configuration', $configArray);
        }
        return $this->_config;
    }

    public function setConfiguration($configArray)
    {
        $this->_config = FF::getSingleton('configuration', $configArray);
    }

    /**
     * @param int $storeId
     * @return \Flagbit_FactFinder_Model_Facade
     */
    public function setStoreId($storeId) {
        $this->_getConfiguration()->setStoreId($storeId);

        return $this;
    }

    /**
     * @return FACTFinder_Abstract_DataProvider
     **/
    protected function _getParallelDataProvider()
    {
        $config = $this->_getConfiguration();
        $params = $this->_getParamsParser()->getServerRequestParams();
        $dp = FACTFinder_Http_ParallelDataProvider::getDataProvider($params, $config, $this->_logger);
        return $dp;
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

    public function getManagementUrl()
    {
        $urlBuilder = $this->_getUrlBuilder();
        $urlBuilder->setAction('Management.ff');
        return $urlBuilder->getNonAuthenticationUrl();
    }

    public function getSuggestUrl()
    {
        $urlBuilder = $this->_getUrlBuilder();
        $urlBuilder->setAction('Suggest.ff');
        $urlBuilder->setParams(array());

        return $urlBuilder->getNonAuthenticationUrl();
    }

    protected function _getUrlBuilder()
    {
        if($this->_urlBuilder === null) {
            $config = $this->_getConfiguration();
            $params = $this->_getParamsParser()->getServerRequestParams();

            $this->_urlBuilder = FF::getInstance('http/urlBuilder', $params, $config, $this->_logger);
        }
        return $this->_urlBuilder;
    }

    public function applyTracking($channel = null)
    {
        return $this->_getFactFinderObject("Scic", "applyTracking", $channel);
    }

    public function getAfterSearchNavigation($channel = null)
    {
        return $this->_getFactFinderObject("Search", "getAsn", $channel);
    }

    public function getCampaigns($channel = null)
    {
        return $this->_getFactFinderObject("Search", "getCampaigns", $channel);
    }

    public function getProductCampaigns($channel = null)
    {
        return $this->_getFactFinderObject("ProductCampaign", "getCampaigns", $channel);
    }

    public function getRecommendations($channel = null)
    {
        return $this->_getFactFinderObject("Recommendation", "getRecommendations", $channel);
    }

    public function getSearchError($channel = null)
    {
        return $this->_getFactFinderObject("Search", "getError", $channel);
    }

    public function getSearchParams($channel = null)
    {
        return $this->_getFactFinderObject("Search", "getSearchParams", $channel);
    }

    public function getSearchResult($channel = null)
    {
        return $this->_getFactFinderObject("Search", "getResult", $channel);
    }

    public function getSearchStackTrace($channel = null)
    {
        return $this->_getFactFinderObject("Search", "getStackTrace", $channel);
    }

    public function getSearchStatus($channel = null)
    {
        return $this->_getFactFinderObject("Search", "getStatus", $channel);
    }

    public function getSuggestions($channel = null)
    {
        return $this->_getFactFinderObject("Suggest", "getSuggestions", $channel);
    }

    public function getTagCloud($channel = null)
    {
        return $this->_getFactFinderObject("TagCloud", "getTagCloud", $channel);
    }

    protected function _getFactFinderObject($adapterType, $objectGetter, $channel = null)
    {
        try {
            $this->_loadAllData();
            $adapterGetter = "get".$adapterType."Adapter";
            return $this->$adapterGetter($channel)->$objectGetter();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getActualFactFinderVersion()
    {
        try {
            $channel = $this->_getConfiguration()->getChannel();
            $this->_loadAllData();
            return $this->_statusHelpers[$channel]->getVersionNumber();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getActualFactFinderVersionString()
    {
        try {
            $channel = $this->_getConfiguration()->getChannel();
            $this->_loadAllData();
            return $this->_statusHelpers[$channel]->getVersionString();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getFactFinderStatus($channel = null)
    {
        try {
            if(!$channel)
                $channel = $this->_getConfiguration()->getChannel();
            $this->_loadAllData();
            return $this->_statusHelpers[$channel]->getStatusCode();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    protected function _loadAllData()
    {
        FACTFinder_Http_ParallelDataProvider::loadAllData();
    }

    public function getRequestParams()
    {
        return $this->_getParamsParser()->getRequestParams();
    }
}