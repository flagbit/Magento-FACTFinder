<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
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
 * Request Processor for click tracking handling
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Model_Processor
{
    const TRACKING_FRONT_NAME = 'ff_tracking';

    /**
     * FactFinder Facade
     *
     * @var FACTFinder_Core_Model_Facade
     */
    protected $_facade;


    /**
     * @var array with loaded config values
     */
    protected $_config;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_initFFAutoloader();
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
            $this->_facade = new FACTFinder_Tracking_Model_Facade($logger, $config);
        }

        return $this->_facade;
    }


     /**
     * Bypass app cache if it's a tracking request
     *
     * @param string $content
     *
     * @return string|bool
     */
    public function extractContent($content)
    {
        if (strpos($this->_getRequestPath(), self::TRACKING_FRONT_NAME) !== false) {
            return false;
        };

        return $content;
    }
    
    /**
     * handle Requests
     *
     * @param string $request
     *
     * @return string
     */
    public function handleRequest($request)
    {
        if (Mage::helper('factfinder')->isInternal()) {
            return;
        }
        $sessionId = Mage::helper('factfinder_tracking')->getSessionId();
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if ($customerId) {
            $customerId = md5('customer_' . $customerId);
        }
        return $this->_getFacade()->getTrackingAdapter()->doTrackingFromRequest($sessionId, $customerId);
    }


    /**
     * Return current page base url
     *
     * @return string
     */
    protected function _getRequestPath()
    {
        $url = false;

        /**
         * Define request URI
         */
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        } elseif (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL'])) {
            $url = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $url = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $url .= $_SERVER['QUERY_STRING'];
            }
        }

        return parse_url($url, PHP_URL_PATH);
    }


}
