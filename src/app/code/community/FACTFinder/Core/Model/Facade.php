<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

require_once BP . DS . 'lib' . DS . 'FACTFinder' . DS . 'Loader.php';
use FACTFinder\Loader as FF;

/**
 * Class FACTFinder_Core_Model_Facade
 *
 * Implements a facade for accessing the FF library
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Facade
{

    /**
     * Two-dimensional array of FACT-Finder adapters
     * First-dimension key corresponds to type
     * Second-dimension key corresponds to channel
     *
     * @var array of FACTFinder\Adapter\AbstractAdapter
     */
    protected $_adapters = array();

    /**
     * @var FACTFinderCustom_Configuration
     */
    protected $_config = null;

    /**
     * @var \FACTFinder\Core\ConfigurationInterface
     */
    protected $_dic = null;

    /**
     * @var \FACTFinder\Data\SearchParameters
     */
    protected $_paramsParser = null;

    /**
     * @var \FACTFinder\Core\Server\UrlBuilder
     */
    protected $_urlBuilder = null;

    /**
     * logger object to log all module internals
     *
     * @var \FACTFinder\Util\LoggerInterface
     */
    protected $_logger = null;

    /**
     * map between known adapters and its state based on its parameters
     *
     * @var array
     */
    protected $_paramHashes = array();

    /**
     * flag if stacktrace was already logged
     *
     * @var null
     */
    protected $_stackTraceLogged = null;


    /**
     * Class constructor
     *
     * @param mixed $arg
     * @param mixed $config
     */
    public function __construct($arg = null, $config = null)
    {
        if ($arg === null || !($arg instanceof FACTFinder\Util\LoggerInterface)) {
            $arg = Mage::helper('factfinder/debug');
        }

        FF::disableCustomClasses();

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
            if (extension_loaded('iconv')) {
                $type = 'Core\IConvEncodingConverter';
            } elseif(function_exists('utf8_encode')
                && function_exists('utf8_decode')
            ) {
                $type = 'Core\Utf8EncodingConverter';
            } else {
                throw new \Exception('No encoding conversion available.');
            }

            return FF::getInstance(
                $type,
                $c['loggerClass'],
                $c['configuration']
            );
        };

        $this->_dic = $dic;

        $this->_logger = $arg;
    }


    /**
     * Retrieve an instance of search adapter
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\Search
     */
    public function getSearchAdapter($channel = null)
    {
        return $this->_getAdapter("search", $channel);
    }


    /**
     *
     * Get an instance of compare adapter
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\Compare
     */
    public function getCompareAdapter($channel = null)
    {
        return $this->_getAdapter("compare", $channel);
    }


    /**
     * Get an instance of import adapter
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\Import
     */
    public function getImportAdapter($channel = null)
    {
        return $this->_getAdapter("import", $channel);
    }


    /**
     * Get an instance of similar records adapter
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\SimilarRecords
     */
    public function getSimilarRecordsAdapter($channel = null)
    {
        return $this->_getAdapter("similarRecords", $channel);
    }


    /**
     * Configure search adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    public function configureSearchAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "search", $channel, $id);
    }


    /**
     * Configure compare adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    public function configureCompareAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "compare", $channel, $id);
    }


    /**
     * Configure import adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    public function configureImportAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "import", $channel, $id);
    }


    /**
     * Configure similar records adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
    public function configureSimilarRecordsAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "similarRecords", $channel, $id);
    }


    /**
     * Configure adapter
     *
     * @param array  $params
     * @param string $type
     * @param string $channel
     * @param int    $id
     *
     * @return void
     */
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
     * Returns the hash that identifies a certain combination of parameters.
     * It represents the current parameter state of the adapter specified by $type, $channel and $id
     *
     * @param string $type    (any adapter type)
     * @param string $channel (default: null => default channel)
     * @param int    $id      (default: null => no special id)
     *
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


    /**
     * Create hash for an array of params
     *
     * @param array $params
     *
     * @return string
     */
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
     * Get identifying hash for each adapter based on type, channel and id
     *
     * @param string $type
     * @param string $channel (default: null)
     * @param int    $id      (default: null)
     *
     * @return string
     */
    protected function _getAdapterIdentifier($type, $channel = null, $id = null)
    {
        $args = func_get_args();

        return implode('_', $args);
    }


    /**
     * Get an instance of adapter
     *
     * @param string $type
     * @param string $channel (default: null)
     * @param int    $id      (default: null)
     *
     * @return \FACTFinder\Adapter\AbstractAdapter
     */
    protected function _getAdapter($type, $channel = null, $id = null)
    {
        $hashKey = $this->_getAdapterIdentifier($type, $channel, $id);

        // get the channel after calculating the adapter identifier
        if (!$channel) {
            $channel = $this->getConfiguration()->getChannel();
        }

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
     * Get current facade configuration
     *
     * @return FACTFinderCustom_Configuration
     */
    public function getConfiguration()
    {
        if ($this->_config == null) {
            $this->_config = $this->_dic['configuration'];
        }

        return $this->_config;
    }


    /**
     * Set facade configuration
     *
     * @param array $configArray
     *
     * @return void
     */
    public function setConfiguration($configArray)
    {
        $this->_config = new FACTFinderCustom_Configuration($configArray);
    }


    /**
     * Set store id
     *
     * @param int $storeId
     *
     * @return FACTFinder_Core_Model_Facade
     */
    public function setStoreId($storeId)
    {
        $this->getConfiguration()->setStoreId($storeId);

        return $this;
    }


    /**
     * Get url for accessing web-interface
     *
     * @return string
     */
    public function getManagementUrl()
    {
        return $this->_getUrlBuilder()
            ->getNonAuthenticationUrl('Management.ff', $this->_dic['requestParser']->getRequestParameters());
    }


    /**
     * Get url builder object
     *
     * @return \FACTFinder\Core\Server\UrlBuilder
     */
    protected function _getUrlBuilder()
    {
        if ($this->_urlBuilder === null) {
            $this->_urlBuilder = $this->_dic['serverUrlBuilder'];
        }

        return $this->_urlBuilder;
    }


    /**
     * Get after search navigation object
     *
     * @param string $channel
     * @param int    $id
     *
     * @return \FACTFinder\Data\AfterSearchNavigation
     */
    public function getAfterSearchNavigation($channel = null, $id = null)
    {
        $result = $this->_getFactFinderObject("search", "getAfterSearchNavigation", $channel, $id);

        $this->_checkStackTrace($channel, $id);

        return $result;
    }


    /**
     * Get search error message
     *
     * @param string|null $channel
     * @param int|null    $id
     *
     * @return string|null
     */
    public function getSearchError($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getError", $channel, $id);
    }


    /**
     * Retrieve search parameters from request
     *
     * @return \FACTFinder\Data\SearchParameters
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
     * @return \FACTFinder\Util\Parameters
     */
    public function getClientRequestParams()
    {
        return $this->_dic['requestParser']->getClientRequestParameters();
    }


    /**
     * Get search result object
     *
     * @param string|null $channel
     * @param int|null    $id
     *
     * @return \FACTFinder\Data\Result
     */
    public function getSearchResult($channel = null, $id = null)
    {
        $result =  $this->_getFactFinderObject("search", "getResult", $channel, $id);

        $this->_checkStackTrace($channel, $id);

        return $result;
    }


    /**
     * Get search stacktrace string
     *
     * @param string|null $channel
     * @param int|null    $id
     *
     * @return string
     */
    public function getSearchStackTrace($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getStackTrace", $channel, $id);
    }


    /**
     * Get search status string
     *
     * @param string $channel
     * @param int    $id
     *
     * @return string
     */
    public function getSearchStatus($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getStatus", $channel, $id);
    }


    /**
     * Get search campaigns
     *
     * @param string $channel
     * @param int    $id
     *
     * @return \FACTFinder\Data\CampaignIterator|null
     */
    public function getSearchCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getCampaigns", $channel, $id);
    }


    /**
     * Get an object of a specified type
     * Returns null in case of an error
     *
     * @param string $type
     * @param string $objectGetter
     * @param string $channel
     * @param int    $id
     *
     * @return Object|null
     */
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


    /**
     * @return \FACTFinder\Core\ConfigurationInterface|object
     */
    public function getDic()
    {
        return $this->_dic;
    }


    /**
     * Create a new results object
     *
     * @param array  $records
     * @param string $refKey
     * @param int    $foundRecordsCount
     *
     * @return \FACTFinder\Data\Result
     */
    public function getNewResultObject($records, $refKey, $foundRecordsCount)
    {
        return FF::getInstance(
            'Data\Result',
            $records,
            $refKey,
            $foundRecordsCount
        );
    }


    /**
     * Get paging object (collection)
     *
     * @param string $channel
     * @param int    $id
     *
     * @return \FACTFinder\Data\Paging
     */
    public function getPaging($channel = null, $id = null)
    {
        $result = $this->_getFactFinderObject('search', 'getPaging', $channel, $id);

        $this->_checkStackTrace($channel, $id);

        return $result;
    }


    /**
     * Get sorting object (collection)
     *
     * @param string $channel
     * @param int    $id
     *
     * @return \FACTFinder\Data\Sorting
     */
    public function getSorting($channel = null, $id = null)
    {
        return $this->_getFactFinderObject('search', 'getSorting', $channel, $id);
    }

    /**
     * Log stack trace if there is one in result
     */
    protected function _checkStackTrace($channel, $id)
    {
        if($this->_stackTraceLogged) {
            return;
        }

        $helper = Mage::helper('factfinder/debug');
        if ($helper->isDebugMode()
            && $this->getSearchError($channel, $id)
            && $this->getSearchStackTrace($channel, $id)
        ) {
            $helper->trace($this->getSearchStackTrace($channel, $id));
            $this->_stackTraceLogged = true;
        }
    }


    /**
     * Trigger data import on FF side
     *
     * @param null|string $channel
     *
     * @return SimpleXMLElement
     */
    public function triggerDataImport($channel = null)
    {
        $this->configureImportAdapter(array('channel' => $channel));

        return $this->getImportAdapter($channel)->triggerDataImport();
    }


}