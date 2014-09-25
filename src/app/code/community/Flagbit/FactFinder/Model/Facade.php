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

    /**
     * map between known adapters and its state based on its parameters
     * @var array
     */
    protected $_paramHashes = array();

    /**
     * @var boolean is set to true, if caching is enabled and can be used
     */
    private $_useCaching = null;

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

    public function getLegacyTrackingAdapter($channel = null)
    {
        return $this->_getAdapter("legacyTracking", $channel);
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
        $adapterId = $this->_getAdapterIdentifier($type, $channel, $id);
        $this->_paramHashes[$adapterId] = $this->_createParametersHash($params);

        foreach($params as $key => $value)
            $adapter->setParam($key, $value);
    }

    /**
     * returns the hash that identifies a certain combination of parameters.
     * It represents the current parameter state of the adapter specified by $type, $channel and $id
     *
     * @param $type (any adapter type)
     * @param $channel (default: null => default channel)
     * @param $id (default: null => no special id)
     * @return string
     */
    protected function _getParametersHash($type, $channel = null, $id = null)
    {
        $returnValue = '';
        $adapterId = $this->_getAdapterIdentifier($type, $channel, $id);
        if (array_key_exists($adapterId, $this->_paramHashes))
        {
            $returnValue = $this->_paramHashes[$adapterId];
        }
        return $returnValue;
    }

    private function _createParametersHash($params)
    {
        $returnValue = '';
        if($params) {
            ksort($params);
            $returnValue = md5(http_build_query($params));
        }
        return $returnValue;
    }

    /**
     * get identifying hash for each adapter based on type, channel and id
     * @param $type
     * @param $channel (default: null)
     * @param $id (default: null)
     * @return string hash
     */
    protected function _getAdapterIdentifier($type, $channel = null, $id = null)
    {
        $args = func_get_args();
        return implode('_', $args);
    }

    /**
     * @return FACTFinder_Abstract_Adapter
     */
    protected function _getAdapter($type, $channel = null, $id = null)
    {
        $format = $this->_getFormat($type);
        $hashKey = $this->_getAdapterIdentifier($type, $channel, $id);

        // get the channel after calculating the adapter identifier
        if(!$channel)
            $channel = $this->_getConfiguration()->getChannel();

        if(!isset($this->_adapters[$hashKey][$channel]))
        {
            $config            = $this->_getConfiguration();
            $encodingHandler   = FF::getSingleton('encodingHandler', $config);
            $dataProvider      = $this->_getParallelDataProvider();
            $dataProvider->setParam('channel', $channel);

            /*
            // new tracking needs session ID and sourceRefKey for every request
            // helper must not be used inside this class, as it is also used without the app context
            // TODO: do it in a different way
            if(!Mage::helper('factfinder')->useOldTracking()) {
                $dataProvider->setParam('sourceRefKey', Mage::getSingleton('core/session')->getFactFinderRefKey());
                $dataProvider->setParam('sid'         , md5(Mage::getSingleton('core/session')->getSessionId()));
            }*/

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
        if (!in_array($type, array('scic', 'suggest', 'legacyTracking'))) {
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

    public function applyTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("tracking", "applyTracking", $channel, $id);
    }

    public function applyScicTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("scic", "applyTracking", $channel, $id);
    }

    public function applyLegacyTracking($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("legacyTracking", "applyTracking", $channel, $id);
    }

    public function getAfterSearchNavigation($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getAsn", $channel, $id);
    }

    public function getCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getCampaigns", $channel, $id);
    }

    public function getProductCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("productCampaign", "getCampaigns", $channel, $id);
    }

    public function getRecommendations($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("recommendation", "getRecommendations", $channel, $id);
    }

    public function getSearchError($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getError", $channel, $id);
    }

    public function getSearchParams($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getSearchParams", $channel, $id);
    }

    public function getSearchResult($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getResult", $channel, $id);
    }

    public function getSearchStackTrace($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getStackTrace", $channel, $id);
    }

    public function getSearchStatus($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getStatus", $channel, $id);
    }

    public function getSuggestions($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("suggest", "getSuggestions", $channel, $id);
    }

    public function getTagCloud($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("tagCloud", "getTagCloud", $channel, $id);
    }

    protected function _getFactFinderObject($type, $objectGetter, $channel = null, $id = null)
    {
        $cacheKey = '';
        $data = null;

        if ($this->_useSearchCaching())
        {
            $adapterId = $this->_getAdapterIdentifier($type, $channel, $id);
            $cacheKey = 'FACTFINDER_'.$adapterId . '_' . $objectGetter .'_'. $this->_getParametersHash($type, $channel, $id);
            if($cache = Mage::app()->loadCache($cacheKey))
            {
                $data = unserialize($cache);
            }
        }

        if ($data == null) {
            try {
                $this->_loadAllData();

                // BUG Potential:
                // if you read this because you got the error message, that you must call
                //  > 'loadAllData' before trying to get data! <
                // this might have happened because you initialized another adapter and not the one
                // that is called here
                $adapter = $this->_getAdapter($type, $channel, $id);
                $data = $adapter->$objectGetter();

                if($this->_useSearchCaching())
                {
                    Mage::app()->saveCache(serialize($data), $cacheKey, array('FACTFINDER_SEARCH'), 600);
                }

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $data;
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

    private function _useSearchCaching()
    {
        if ($this->_useCaching == null)
        {
            // caching only works from version 5.3 because of php bug 45706 (http://bugs.php.net/45706):
            // because of it, the asn objects can't be serialized and cached
            // this bug was fixed with 5.3.0 (http://www.php.net/ChangeLog-5.php)
            $this->_useCaching = (version_compare(PHP_VERSION, '5.3.0') >= 0 && Mage::app()->useCache('factfinder_search'));
        }
        return $this->_useCaching;
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