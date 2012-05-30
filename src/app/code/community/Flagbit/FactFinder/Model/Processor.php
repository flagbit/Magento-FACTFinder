<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Model class
 *
 * Request Processor for fast handling
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_Processor
{

    const CACHE_TAG  = 'FACTFINDER';  // Cache Tag
    const REQUEST_ID_PREFIX = 'FACTFINDER_';
    const XML_CONFIG_PATH = 'factfinder/search/';


    /**
     * Search Adapter
     * @var Flagbit_FactFinder_Model_Adapter
     */
    protected $_searchAdapter;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $uri = $this->_getFullPageUrl();

        $this->_requestId       = $uri;
        $this->_requestCacheId  = $this->prepareCacheId($this->_requestId);
        $this->_requestTags     = array(self::CACHE_TAG);
    }

    /**
     * get Fact-Finder Search Adapter
     *
     * @return Flagbit_FactFinder_Model_Adapter
     */
    public function getSearchAdapter()
    {
    	if($this->_searchAdapter === null){
			$logger = new Flagbit_FactFinder_Helper_Debug();
    		$this->_searchAdapter = new Flagbit_FactFinder_Model_Adapter($logger);
    	}
    	return $this->_searchAdapter;
    }


    /**
     * Get page content from cache storage
     *
     * @param string $content
     * @return string | false
     */
    public function extractContent($content)
    {
    	// handle in App Request if "factfinder" in Request path
        if (!$content
        	&& strpos($this->_requestId, 'factfinder')
        	&& $this->isAllowed()) {

            $requestCacheId = $this->prepareCacheId($this->getRequestId().'request');
            $request = Mage::app()->loadCache($requestCacheId);
            if ($request) {
				$content = $this->handleWithoutAppRequest($request);
            }

        }
        return $content;
    }

    /**
     * handle in App Requests
     *
     * @param string $request
     * @return string
     */
    public function handleInAppRequest($request)
    {
        $requestCacheId = $this->prepareCacheId($this->getRequestId().'request');
        Mage::app()->saveCache($request, $requestCacheId, $this->getRequestTags());

        $configCacheId = $this->prepareCacheId($this->getRequestId().'config');
        Mage::app()->saveCache(serialize(Mage::getStoreConfig('factfinder/search')), $configCacheId, $this->getRequestTags());

    	return $this->_handleRequest($request);
    }

    /**
     * hanlde without App Requests
     *
     * @param string $request
     * @return string
     */
    public function handleWithoutAppRequest($request)
    {
    	$configCacheId = $this->prepareCacheId($this->getRequestId().'config');
    	$config = null;
    	try{
    		$config = unserialize(Mage::app()->loadCache($configCacheId));
    	} catch (Exception $e){
    		return;
    	}
    	if(!is_array($config) || empty($config)){
    		return;
    	}
    	$this->getSearchAdapter()->setConfiguration($config);
    	return $this->_handleRequest($request);
    }

    /**
     * handle Requests
     *
     * @param unknown_type $request
     * @return string
     */
    protected function _handleRequest($request)
    {
		switch ($request){

			case 'factfinder_proxy_scic':
		        $scic = $this->getSearchAdapter()->getScicAdapter();
		        return $scic->doTrackingFromRequest();
				break;

			case 'factfinder_proxy_suggest':
		        return $this->getSearchAdapter()->getSuggestResultJsonp($this->_getRequestParam('query'), $this->_getRequestParam('jquery_callback'));
				break;

		}
    }

    /**
     * get Request Param by Key
     *
     * @param unknown_type $key
     * @return string
     */
    protected function _getRequestParam($key)
    {
    	$value = null;
    	if(isset($_REQUEST[$key])){
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
                $uri.= $_SERVER['REQUEST_URI'];
            } elseif (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL'])) {
                $uri.= $_SERVER['UNENCODED_URL'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $uri.= $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $uri.= $_SERVER['QUERY_STRING'];
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
        return $this->_requestId;
    }

    /**
     * Get page identifier for loading page from cache
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
        if (!$this->_requestId) {
            return false;
        }
        if (isset($_COOKIE['NO_CACHE'])) {
            return false;
        }
        if (isset($_GET['no_cache'])) {
            return false;
        }

        return true;
    }

    /**
     * Get cache request associated tags
     * @return array
     */
    public function getRequestTags()
    {
        return $this->_requestTags;
    }

}