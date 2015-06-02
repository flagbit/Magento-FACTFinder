<?php

class FACTFinder_Core_Helper_Search extends Mage_Core_Helper_Abstract
{
    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_PRODUCT_IDENTIFIER = 'factfinder/config/identifier';

    const CACHE_TAG = 'FACTFINDER';
    const CACHE_ID = "FallbackCache";
    const REQUEST_ID_PREFIX = 'FACTFINDER_';

    protected static $_skipFactFinder = null;

    protected static $_isFallbackFeatureActive = null;

    protected static $_failedAttemptRegistered = false;

    /**
     * Retrieve query model object
     *
     * @return String
     */
    public function getQueryText()
    {
        return Mage::helper('catalogsearch')->getQueryText();
    }


    /**
     * get Page Limit
     *
     * @return int
     */
    public function getPageLimit()
    {
        $limit = $this->_getToolbarBlock()->getLimit();
        if ($limit == 'all') {
            $limit = 2 * 3 * 4 * 5 * 6; //a lot of products working for each layout
        }
        return $limit;
    }


    /**
     * get Toolbar Block
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    protected function _getToolbarBlock()
    {
        $mainBlock = Mage::app()->getLayout()->getBlock('search.result');
        if ($mainBlock instanceof Mage_CatalogSearch_Block_Result) {
            $toolbarBlock = $mainBlock->getListBlock()->getToolbarBlock();
        } else {
            $toolbarBlock = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar');
        }

        return $toolbarBlock;
    }


    /**
     * get current Page Number
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_getToolbarBlock()->getCurrentPage();
    }


    /**
     * get Entity ID Field Name by Configuration or via Entity
     *
     * @return string
     */
    public function getIdFieldName()
    {
        $idFieldName = Mage::getStoreConfig(self::XML_CONFIG_PATH_PRODUCT_IDENTIFIER);
        if (!$idFieldName) {
            $idFieldName = $this->getEntity()->getIdFieldName();
        }
        return $idFieldName;
    }


    /**
     * Determines whether the fallback should be used
     *
     * @return bool
     **/
    protected function _isFallbackFeatureActive()
    {
        if (self::$_isFallbackFeatureActive === null) {
            self::$_isFallbackFeatureActive = Mage::getStoreConfig('factfinder/fallback/use_fallback');
        }

        return self::$_isFallbackFeatureActive;
    }


    /**
     * Enable fallback feature
     *
     * @param int $delay
     */
    protected function _enableFallback($delay)
    {
        self::$_skipFactFinder = true;
        $nextRetryTimestamp = intval(time() / 60) + $delay;
        Mage::app()->saveCache($nextRetryTimestamp, $this->_getCacheId('nextRetryTimestamp'), array(self::CACHE_TAG));
    }


    /**
     * Disable fallback feature
     */
    protected function _disableFallback()
    {
        self::$_skipFactFinder = false;
        $nextRetryTimestamp = 0;
        Mage::app()->saveCache($nextRetryTimestamp, $this->_getCacheId('nextRetryTimestamp'), array(self::CACHE_TAG));
    }


    /**
     * Determines whether FACT-Finder should be skipped completely, because it has failed to respond too often
     * The check is made lazily so that it will return the same result for every call during one request to Magento.
     *
     * @return bool
     **/
    protected function _skipFactFinder()
    {
        if (self::$_skipFactFinder === null) {
            if (!$this->_isFallbackFeatureActive()) {
                self::$_skipFactFinder = false;
            } else {
                $nextRetryTimestamp = intval(Mage::app()->loadCache($this->_getCacheId('nextRetryTimestamp')));
                $currentTimestamp = intval(time() / 60);

                self::$_skipFactFinder = ($currentTimestamp <= $nextRetryTimestamp);
            }
        }

        return self::$_skipFactFinder;
    }


    /**
     * resets all fallback counter values.
     *
     * @return void
     */
    public function resetFailedAttemptCount()
    {
        $this->_disableFallback();
        $this->_saveFailedAttempts(array());
    }


    /**
     * Registers that FACT-Finder has failed to respond.
     * The attempt will be represented as an integer corresponding to attempt's timestamp in minutes.
     * Only one failed attempt per lifetime of this object will be registered.
     **/
    public function registerFailedAttempt()
    {
        if (self::$_failedAttemptRegistered || !$this->_isFallbackFeatureActive() || $this->_skipFactFinder()) {
            return;
        }

        $failedAttempts = $this->_loadFailedAttempts();
        $failedAttempts = $this->_removeOldEntries($failedAttempts);
        $failedAttempts[] = intval(time() / 60);
        $this->_saveFailedAttempts($failedAttempts);

        self::$_failedAttemptRegistered = true;
        Mage::helper('factfinder/debug')->log('Registered failed attempt to connect to FACT-Finder. ' . count($failedAttempts) . ' failed attempts registered.');

        if (count($failedAttempts) >= 3) {
            $delay = Mage::getStoreConfig('factfinder/fallback/wait_time');

            $this->_enableFallback($delay);

            // don't output a warning, if the delay is set to 0 as this would cause a lot of messages during a factfinder downtime
            if ($delay > 0) {
                $this->_outputWarningMessage();
            }
        }
    }


    /**
     * Assemble and output warning message
     */
    protected function _outputWarningMessage()
    {
        $delay = Mage::getStoreConfig('factfinder/fallback/wait_time');

        $title = 'FACT-Finder unreachable! Falling back to Magento\'s search for ' . $delay . ' minutes.';
        $message = 'FACT-Finder did not respond for the third time. Magento will now use its own search for '
            . $delay . ' minutes before trying to reach FACT-Finder again. '
            . 'If the problem persists, please check your FACT-Finder server and the settings'
            . ' in Magento\'s FACT-Finder configuration.';

        $adminNotificationInbox = Mage::getModel('adminnotification/inbox');

        if (method_exists($adminNotificationInbox, 'addMajor')) {
            Mage::getModel('adminnotification/inbox')->addMajor($title, $message);
        } else {
            $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR;
            $date = date('Y-m-d H:i:s');

            $adminNotificationInbox->parse(array(
                array(
                    'severity'    => $severity,
                    'date_added'  => $date,
                    'title'       => $title,
                    'description' => $message,
                    'url'         => '',
                    'internal'    => true
                )
            ));
        }
    }


    /**
     * Get id for cache items
     *
     * @param string $suffix
     *
     * @return string
     */
    protected function _getCacheId($suffix = null)
    {
        $cacheId = self::REQUEST_ID_PREFIX . self::CACHE_ID;
        if ($suffix != null) {
            $cacheId .= '_' . $suffix;
        }

        return $cacheId;
    }


    /**
     * Loads previously registered failed attempts from cache, if they exist.
     * Returns an empty array, otherwise.
     *
     * @return    array
     */
    protected function _loadFailedAttempts()
    {
        $cachedContent = Mage::app()->loadCache($this->_getCacheId('failedAttempts'));
        $failedAttempts = array();
        if ($cachedContent) {
            $failedAttempts = unserialize($cachedContent);
        }

        return $failedAttempts;
    }


    /**
     * Save failed attempts to cache.
     *
     * @param array $failedAttempts
     */
    public function _saveFailedAttempts($failedAttempts)
    {
        Mage::app()->saveCache(serialize($failedAttempts), $this->_getCacheId('failedAttempts'), array(self::CACHE_TAG));
    }


    /**
     * Removes entries from a list of minute-timestamps which are older than 3 minutes
     *
     * @param array $entries
     *
     * @return array
     */
    protected function _removeOldEntries($entries)
    {
        $newEntries = array();

        $minutesTimestamp = intval(time() / 60);
        foreach ($entries as $entry) {
            if ($minutesTimestamp - $entry < 3)
                $newEntries[] = $entry;
        }

        return $newEntries;
    }


    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        return $this->_getToolbarBlock()->getDefaultPerPageValue();
    }


    /**
     * get current Order
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        return $this->_getToolbarBlock()->getCurrentOrder();
    }


    /**
     * get current Order Direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        return $this->_getToolbarBlock()->getCurrentDirection();
    }


    /**
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        return Mage::helper('catalogsearch')->getQuery();
    }


    /**
     * Get Module Status depending on Module
     *
     * @return bool
     */
    public function getIsOnSearchPage()
    {
        $moduleName = Mage::app()->getRequest()->getModuleName();
        if ($moduleName == 'catalogsearch'
            || ($moduleName == 'xmlconnect' && strpos(Mage::app()->getRequest()->getActionName(), 'search') !== false)
        ) {
            return true;
        }

        return false;
    }


}
