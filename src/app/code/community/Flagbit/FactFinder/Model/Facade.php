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

    protected function _loadAllData()
    {
        FACTFinder_Http_ParallelDataProvider::loadAllData();
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
     * @return string
     */
    public function getManagementUrl()
    {
        $urlBuilder = $this->_getUrlBuilder();
        $urlBuilder->setType('Management.ff');
        return $urlBuilder->getNonAuthenticationUrl();
    }

    /**
     * @return string
     */
    public function getSuggestUrl()
    {
        $urlBuilder = $this->_getUrlBuilder();
        $urlBuilder->setType('Suggest.ff');
        $urlBuilder->setParams(array());

        return $urlBuilder->getNonAuthenticationUrl();
    }

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

    public function getSearchStatus($channel = null)
    {
        try {
            $this->_loadAllData();
            return $this->getSearchAdapter($channel)->getStatus();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
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
