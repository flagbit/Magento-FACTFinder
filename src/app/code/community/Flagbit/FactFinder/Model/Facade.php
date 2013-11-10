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
     * @var FACTFinder_Http_DataProvider
     */
    protected $_dataProvider = null;

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

    public function getSearchAdapter($channel = null)
    {
        return $this->_getAdapter("search", $channel);
    }

    public function getScicAdapter($channel = null)
    {
        return $this->_getAdapter("scic", $channel);
    }

    public function getSuggestAdapter($channel = null)
    {
        return $this->_getAdapter("suggest", $channel);
    }

    public function getRecommendationAdapter($channel = null)
    {
        return $this->_getAdapter("recommendation", $channel);
    }

    public function getTagCloudAdapter($channel = null)
    {
        return $this->_getAdapter("tagCloud", $channel);
    }

    public function getCompareAdapter($channel = null)
    {
        return $this->_getAdapter("compare", $channel);
    }

    public function getImportAdapter($channel = null)
    {
        return $this->_getAdapter("import", $channel);
    }

    public function getProductCampaignAdapter($channel = null)
    {
        return $this->_getAdapter("productCampaign", $channel);
    }

    public function getSimilarRecordsAdapter($channel = null)
    {
        return $this->_getAdapter("similarRecords", $channel);
    }

    public function getTrackingAdapter($channel = null)
    {
        return $this->_getAdapter("tracking", $channel);
    }

    public function configureSearchAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "search", $channel, $id);
    }

    public function configureScicAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "scic", $channel, $id);
    }

    public function configureSuggestAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "suggest", $channel, $id);
    }

    public function configureRecommendationAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "recommendation", $channel, $id);
    }

    public function configureTagCloudAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "tagCloud", $channel, $id);
    }

    public function configureCompareAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "compare", $channel, $id);
    }

    public function configureImportAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "import", $channel, $id);
    }

    public function configureProductCampaignAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "productCampaign", $channel, $id);
    }

    public function configureSimilarRecordsAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "similarRecords", $channel, $id);
    }

    public function configureTrackingAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "tracking", $channel, $id);
    }

    protected function _configureAdapter($params, $type, $channel = null, $id = null)
    {
        $adapter = $this->_getAdapter($type, $channel, $id);

        foreach($params as $key => $value)
            $adapter->setParam($key, $value);
    }

    /**
     * @return FACTFinder_Abstract_Adapter
     */
    protected function _getAdapter($type, $channel = null, $id = null)
    {
        $format = $this->_getFormat($type);

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
        $dataProvider = $this->_getDataProvider();
        $dataProvider->setType('Suggest.ff');

        return $dataProvider->getAuthenticationUrl();
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

    protected function _getDataProvider()
    {
        if($this->_dataProvider === null) {
            $config = $this->_getConfiguration();
            $params = $this->_getParamsParser()->getServerRequestParams();

            $this->_dataProvider = FF::getInstance('http/dataProvider', $params, $config, $this->_logger);
        }
        return $this->_dataProvider;
    }

    public function applyTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Tracking", "applyTracking", $channel, $id);
    }

    public function applyScicTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Scic", "applyTracking", $channel, $id);
    }

    public function getAfterSearchNavigation($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Search", "getAsn", $channel, $id);
    }

    public function getCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Search", "getCampaigns", $channel, $id);
    }

    public function getProductCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("ProductCampaign", "getCampaigns", $channel, $id);
    }

    public function getRecommendations($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Recommendation", "getRecommendations", $channel, $id);
    }

    public function getSearchError($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Search", "getError", $channel, $id);
    }

    public function getSearchParams($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Search", "getSearchParams", $channel, $id);
    }

    public function getSearchResult($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Search", "getResult", $channel, $id);
    }

    public function getSearchStackTrace($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Search", "getStackTrace", $channel, $id);
    }

    public function getSearchStatus($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Search", "getStatus", $channel, $id);
    }

    public function getSuggestions($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("Suggest", "getSuggestions", $channel, $id);
    }

    public function getTagCloud($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("TagCloud", "getTagCloud", $channel, $id);
    }

    protected function _getFactFinderObject($adapterType, $objectGetter, $channel = null, $id = null)
    {
        $args = func_get_args();
        $cacheKey = 'FACTFINDER_'.implode('_',$args).'_' . md5(serialize($this->_getParamsParser()));

        if(Mage::app()->useCache('factfinder_search') && $cache = Mage::app()->loadCache($cacheKey))
        {
            return unserialize($cache);
        }

        try {
            $this->_loadAllData();
            $adapterGetter = "get".$adapterType."Adapter";

            $data = $this->$adapterGetter($channel, $id)->$objectGetter();

            if(Mage::app()->useCache('factfinder_search'))
            {
                Mage::app()->saveCache(serialize($data), $cacheKey, array('FACTFINDER_SEARCH'), 600);
            }

            return $data;
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
