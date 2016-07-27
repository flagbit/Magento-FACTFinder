<?php
/**
 * FACTFinder_Suggest
 *
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

require_once BP . DS . 'lib' . DS . 'FACTFinder' . DS . 'Loader.php';

/**
 * Model class
 *
 * Request Processor for fast handling
 *
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_Model_Processor
{

    const CACHE_TAG = 'FACTFINDER';  // Cache Tag
    const REQUEST_ID_PREFIX = 'FACTFINDER_';
    const XML_CONFIG_PATH = 'factfinder/search/';

    /**
     * FactFinder Facade
     *
     * @var FACTFinder_Core_Model_Facade
     */
    protected $_facade;


    /**
     * Class constructor
     */
    public function __construct()
    {
        $uri = $this->_getFullPageUrl();

        $this->_initFFAutoloader();

        $this->_requestId = $uri;
        $this->_requestCacheId = $this->prepareCacheId($this->_requestId);
        $this->_requestTags = array(self::CACHE_TAG);
    }


    /**
     * Init fact-finder lib autoloader
     *
     * @return void
     */
    protected function _initFFAutoloader()
    {
        $autoloaderClass = new FACTFinder_Core_Model_Autoloader();
        $autoloaderClass->addAutoloader(new Varien_Event_Observer());
    }


    /**
     * Get Fact-Finder Facade
     * we do it manually, because we do not have the full magento context
     *
     * @param mixed $config
     *
     * @return FACTFinder_Core_Model_Facade
     */
    protected function _getFacade($config = null)
    {
        if ($this->_facade === null) {
            $logger = new FACTFinder_Core_Helper_Debug();
            $this->_facade = new FACTFinder_Suggest_Model_Facade($logger, $config);
        }

        return $this->_facade;
    }


    /**
     * Get page content from cache storage
     *
     * @param string $content
     *
     * @return string|false
     */
    public function extractContent($content)
    {
        // handle in App Request if "ff_suggest" in Request path
        if (empty($content)
            && strpos($this->_requestId, 'ff_suggest')
            && $this->isAllowed()
        ) {
            $requestCacheId = $this->prepareCacheId($this->getRequestId() . 'request');
            $request = Mage::app()->loadCache($requestCacheId);
            if ($request) {
                $content = $this->handleWithoutAppRequest();
            }
        }

        return $content;
    }


    /**
     * handle in App Requests
     *
     * @param string $request
     *
     * @return string
     */
    public function handleInAppRequest($request)
    {
        $requestCacheId = $this->prepareCacheId($this->getRequestId() . 'request');
        Mage::app()->saveCache($request, $requestCacheId, $this->getRequestTags());

        $configCacheId = $this->prepareCacheId($this->getRequestId() . 'config');
        Mage::app()->saveCache(
            serialize(Mage::getStoreConfig('factfinder/search')),
            $configCacheId, $this->getRequestTags()
        );

        return $this->_handleRequest();
    }


    /**
     * Hanlde without App Requests
     *
     * @return string
     */
    public function handleWithoutAppRequest()
    {
        $configCacheId = $this->prepareCacheId($this->getRequestId() . 'config');
        $config = null;

        try {
            $config = unserialize(Mage::app()->loadCache($configCacheId));
        } catch (Exception $e) {
            return '';
        }

        if (!is_array($config) || empty($config)) {
            return '';
        }

        $this->_getFacade($config);

        return $this->_handleRequest();
    }


    /**
     * Handle Requests
     *
     * @return string
     */
    protected function _handleRequest()
    {
        $handler = new FACTFinder_Suggest_Model_Handler_Suggest(
            $this->_getRequestParam('query'),
            $this->_getRequestParam('jquery_callback'),
            $this->_getFacade()
        );

        return $handler->getSuggestions();
    }


    /**
     * Get Request Param by Key
     *
     * @param string $key
     *
     * @return string
     */
    protected function _getRequestParam($key)
    {
        $value = null;
        if (isset($_REQUEST[$key])) {
            $value = $_REQUEST[$key];
        }

        return $value;
    }


    /**
     * Return current page base url
     *
     * @return string
     */
    protected function _getFullPageUrl()
    {
        $uri = false;
        /**
         * Define server HTTP HOST
         */
        if (isset($_SERVER['HTTP_HOST'])) {
            $uri = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $uri = $_SERVER['SERVER_NAME'];
        }

        /**
         * Define request URI
         */
        if ($uri) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $uri .= $_SERVER['REQUEST_URI'];
            } elseif (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL'])) {
                $uri .= $_SERVER['UNENCODED_URL'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $uri .= $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $uri .= $_SERVER['QUERY_STRING'];
                }
            }
        }

        $pieces = explode('?', $uri);
        $uri = array_shift($pieces);

        return $uri;
    }


    /**
     * Prepare page identifier
     *
     * @param string $id
     *
     * @return string
     */
    public function prepareCacheId($id)
    {
        return self::REQUEST_ID_PREFIX . md5($id);
    }


    /**
     * Get HTTP request identifier
     *
     * @return string
     */
    public function getRequestId()
    {
        return $this->_requestId . (isset($_COOKIE['store']) ? $_COOKIE['store'] : '');
    }


    /**
     * Get page identifier for loading page from cache
     *
     * @return string
     */
    public function getRequestCacheId()
    {
        return $this->_requestCacheId;
    }


    /**
     * Check if processor is allowed for current HTTP request.
     * Disable processing HTTPS requests and requests with "NO_CACHE" cookie
     *
     * @return bool
     */
    public function isAllowed()
    {
        if (!$this->_requestId
            || isset($_COOKIE['NO_CACHE'])
            || isset($_GET['no_cache'])
        ) {
            return false;
        }

        return true;
    }


    /**
     * Get cache request associated tags
     *
     * @return array
     */
    public function getRequestTags()
    {
        return $this->_requestTags;
    }


}
