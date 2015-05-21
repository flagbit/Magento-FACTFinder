<?php

require_once BP . DS . 'lib' . DS . 'FACTFinder' . DS . 'Loader.php';

use FACTFinder\Loader as FF;

class FACTFinder_Core_Model_Facade
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
     * @var FACTFinder_Abstract_Configuration
     */
    protected $_dic = null;

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

    public function __construct($arg = null, $config = null)
    {
        if ($arg === null || !($arg instanceof FACTFinder\Util\LoggerInterface)) {
            $arg = Mage::helper('factfinder/debug');
        }

        $dic = FF::getInstance('Util\Pimple');

        $dic['loggerClass'] = function ($c) use ($arg) {
            return $arg;
        };

        $dic['configuration'] = function ($c) use ($config) {
            return new FACTFinderCustom_Configuration($config);
        };

        $dic['request'] = $dic->factory(function ($c) {
            return $c['requestFactory']->getRequest();
        });

        $dic['requestFactory'] = function ($c) {
            return FF::getInstance(
                'Core\Server\MultiCurlRequestFactory',
                $c['loggerClass'],
                $c['configuration'],
                $c['requestParser']->getRequestParameters()
            );
        };

        $dic['clientUrlBuilder'] = function ($c) {
            return FF::getInstance(
                'Core\Client\UrlBuilder',
                $c['loggerClass'],
                $c['configuration'],
                $c['requestParser'],
                $c['encodingConverter']
            );
        };

        $dic['serverUrlBuilder'] = function ($c) {
            return FF::getInstance(
                'Core\Server\UrlBuilder',
                $c['loggerClass'],
                $c['configuration']
            );
        };

        $dic['requestParser'] = function ($c) {
            return FF::getInstance(
                'Core\Client\RequestParser',
                $c['loggerClass'],
                $c['configuration'],
                $c['encodingConverter']
            );
        };

        $dic['encodingConverter'] = function ($c) {
            if (extension_loaded('iconv'))
                $type = 'Core\IConvEncodingConverter';
            else if (function_exists('utf8_encode')
                && function_exists('utf8_decode')
            )
                $type = 'Core\Utf8EncodingConverter';
            else
                throw new \Exception('No encoding conversion available.');

            return FF::getInstance(
                $type,
                $c['loggerClass'],
                $c['configuration']
            );
        };

        $this->_dic = $dic;

//        FF::setLogger($arg);
        $this->_logger = $arg;
    }

    public function getSearchAdapter($channel = null)
    {
        return $this->_getAdapter("search", $channel);
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

    public function configureSearchAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "search", $channel, $id);
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

    protected function _configureAdapter($params, $type, $channel = null, $id = null)
    {
        $adapterId = $this->_getAdapterIdentifier($type, $channel, $id);
        $this->_paramHashes[$adapterId] = $this->_createParametersHash($params);

        foreach ($params as $key => $value) {
            $this->_dic['requestParser']->getClientRequestParameters()->set($key, $value);
            $this->_dic['requestParser']->getRequestParameters()->set($key, $value);
        }
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
        if (array_key_exists($adapterId, $this->_paramHashes)) {
            $returnValue = $this->_paramHashes[$adapterId];
        }
        return $returnValue;
    }

    private function _createParametersHash($params)
    {
        $returnValue = '';
        if ($params) {
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
        $hashKey = $this->_getAdapterIdentifier($type, $channel, $id);

        // get the channel after calculating the adapter identifier
        if (!$channel)
            $channel = $this->getConfiguration()->getChannel();

        if (!isset($this->_adapters[$hashKey][$channel])) {
            $this->_adapters[$hashKey][$channel] = FF::getInstance(
                'Adapter\\' . ucfirst($type),
                $this->_dic['loggerClass'],
                $this->_dic['configuration'],
                $this->_dic['request'],
                $this->_dic['clientUrlBuilder']
            );
        }
        return $this->_adapters[$hashKey][$channel];
    }

    /**
     * @return FACTFinderCustom_Configuration config
     */
    public function getConfiguration($configArray = null)
    {
        if ($this->_config == null) {
            $this->_config = $this->_dic['configuration'];
        }
        return $this->_config;
    }

    public function setConfiguration($configArray)
    {
        $this->_config = new FACTFinderCustom_Configuration($configArray);
    }

    /**
     * @param int $storeId
     * @return \Flagbit_FactFinder_Model_Facade
     */
    public function setStoreId($storeId)
    {
        $this->getConfiguration()->setStoreId($storeId);

        return $this;
    }

    public function getManagementUrl()
    {
        return $this->_getUrlBuilder()
            ->getNonAuthenticationUrl('Management.ff', $this->_dic['requestParser']->getRequestParameters());
    }

    protected function _getUrlBuilder()
    {
        if ($this->_urlBuilder === null) {
            $this->_urlBuilder = $this->_dic['serverUrlBuilder'];
        }
        return $this->_urlBuilder;
    }

    public function getAfterSearchNavigation($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getAfterSearchNavigation", $channel, $id);
    }

    public function getCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getCampaigns", $channel, $id);
    }

    public function getProductCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("productCampaign", "getCampaigns", $channel, $id);
    }

    public function getSearchError($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getError", $channel, $id);
    }

    /**
     * Retrieve search parameters from request
     *
     * @return FACTFinder_ParametersParser|object
     */
    public function getSearchParams()
    {
        if ($this->_paramsParser == null) {
            $this->_paramsParser = FF::getInstance(
                'Data\SearchParameters',
                $this->_dic['requestParser']->getRequestParameters()
            );
        }
        return $this->_paramsParser;
    }


    /**
     * Get raw client parameters
     *
     * @return FACTFinder\Util\Parameters
     */
    public function getClientRequestParams()
    {
        return $this->_dic['requestParser']->getClientRequestParameters();
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

    protected function _getFactFinderObject($type, $objectGetter, $channel = null, $id = null)
    {
        $data = null;

        try {
            $adapter = $this->_getAdapter($type, $channel, $id);
            $data = $adapter->$objectGetter();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $data;
    }

    public function getActualFactFinderVersion()
    {
        try {
            $channel = $this->getConfiguration()->getChannel();
            return $this->_statusHelpers[$channel]->getVersionNumber();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    private function _useSearchCaching()
    {
        if ($this->_useCaching == null) {
            // caching only works from version 5.3 because of php bug 45706 (http://bugs.php.net/45706):
            // because of it, the asn objects can't be serialized and cached
            // this bug was fixed with 5.3.0 (http://www.php.net/ChangeLog-5.php)
            $this->_useCaching = (version_compare(PHP_VERSION, '5.3.0') >= 0 && Mage::app()->useCache('factfinder_search'));
        }
        return $this->_useCaching;
    }


    /**
     * Get actual version of FF
     *
     * @return null|string
     */
    public function getActualFactFinderVersionString()
    {
        try {
            $channel = $this->getConfiguration()->getChannel();
            return $this->_statusHelpers[$channel]->getVersionString();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }


    /**
     * Get current status of FF
     *
     * @param $channel
     *
     * @return null
     */
    public function getFactFinderStatus($channel = null)
    {
        try {
            if (!$channel)
                $channel = $this->getConfiguration()->getChannel();
            return $this->_statusHelpers[$channel]->getStatusCode();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
    }

    public function getDic()
    {
        return $this->_dic;
    }

    public function getNewResultObject($records, $refKey, $foundRecordsCount)
    {
        return FF::getInstance(
            'Data\Result',
            $records,
            $refKey,
            $foundRecordsCount
        );
    }
}