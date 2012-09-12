<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Helper class
 *
 * This helper class provides some Methods which allows us
 * to get default und current Values from Toolbar block.
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Helper_Search extends Mage_Core_Helper_Abstract {

    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_PRODUCT_IDENTIFIER = 'factfinder/config/identifier';

    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_USE_PROXY = 'factfinder/config/proxy';
	
    const CACHE_TAG  = 'FACTFINDER';
	const CACHE_ID = "FallbackCache";
    const REQUEST_ID_PREFIX = 'FACTFINDER_';

	protected static $_skipFactFinder = null;
	
	protected static $_isFallbackActive = null;
	
	protected static $_failedAttemptRegistered = false;
	
    /**
     * if FACT-Finder enabled?
     *
     * @return boolean
     */
    public function getIsEnabled($searchPageCheck = true, $functionality = '')
    {
        if (!Mage::getStoreConfigFlag('factfinder/search/enabled')
            || Mage::getStoreConfigFlag('advanced/modules_disable_output/Flagbit_FactFinder')
            || ($searchPageCheck == true && !$this->getIsOnSearchPage() && !Mage::getStoreConfigFlag('factfinder/activation/navigation'))
			|| $this->_skipFactFinder()) {
            return false;
        }

        $result = true;

        if ($functionality) {
            switch ($functionality) {
                case 'suggest':
                    $result = Mage::getStoreConfig('factfinder/activation/suggest');
                break;
                case 'asn':
                    if (Mage::helper('factfinder/search')->getIsOnSearchPage()) {
                        $result = Mage::getStoreConfig('factfinder/activation/asn');
                    }
                    else {
                        $result = Mage::getStoreConfig('factfinder/activation/navigation');
                    }
                break;
                case 'campaign':
                    $result = Mage::getStoreConfig('factfinder/activation/campaign');
                break;
                case 'clicktracking':
                    $result = Mage::getStoreConfig('factfinder/export/clicktracking');
                break;
                case 'tagcloud':
                    $result = Mage::getStoreConfig('factfinder/activation/tagcloud');
                break;
            }
        }

        return $result;
    }
	
	/**
	 * Determines whether the fallback should be used
	 *
	 * @return	bool
	 **/
	protected function _isFallbackActive()
	{
		if(self::$_isFallbackActive === null)
		{
			self::$_isFallbackActive = Mage::getStoreConfig('factfinder/fallback/use_fallback');
		}
		
		return self::$_isFallbackActive;
	}
	
	/**
	 * Determines whether FACT-Finder should be skipped completely, because it has failed to respond too often
	 * The check is made lazily so that it will return the same result for every call during one request to Magento.
	 *
	 * @return bool
	 **/
	protected function _skipFactFinder()
	{
		if(self::$_skipFactFinder === null)
		{
			if(!$this->_isFallbackActive())
			{
				self::$_skipFactFinder = false;
			}
			else
			{
				$failedAttempts = $this->_loadFailedAttempts();
				$failedAttempts = $this->_removeOldEntries($failedAttempts);
				$this->saveFailedAttempts($failedAttempts);
				
				self::$_skipFactFinder = (count($failedAttempts) >= 3);
		
				if(self::$_skipFactFinder)
				{
					Mage::helper('factfinder/debug')->log('Failed to connect to FACT-Finder 3 times. Falling back to Magento\'s search.');
				}
			}
		}
		
		return self::$_skipFactFinder;
	}
	
	protected function _getCacheId()
	{
		return self::REQUEST_ID_PREFIX . self::CACHE_ID;
	}
	
	/**
	 * Registers that FACT-Finder has failed to respond.
	 * The attempt will be represented as an integer corresponding to attempt's timestamp in minutes.
	 * Only one failed attempt per lifetime of this object will be registered.
	 **/
	public function registerFailedAttempt()
	{
		if(self::$_failedAttemptRegistered)
			return;
		
		$failedAttempts = $this->_loadFailedAttempts();
		$failedAttempts[] = intval(time() / 60);
		$this->saveFailedAttempts($failedAttempts);
		
		self::$_failedAttemptRegistered = true;
		
		Mage::helper('factfinder/debug')->log('Registered failed attempt to connect to FACT-Finder. '.count($failedAttempts).' failed attempts registered.');
		if(count($failedAttempts) >= 3)
		{
			$delay = Mage::getStoreConfig('factfinder/fallback/wait_time');
			Mage::getModel('adminnotification/inbox')->addMajor(
				'FACT-Finder unreachable! Falling back to Magento\'s search for '.$delay.' minutes.',
				'FACT-Finder did not respond for the third time. Magento will now use its own search for '.$delay.' minutes before trying to reach FACT-Finder again. If the problem persists, please check your FACT-Finder server and the settings in Magento\'s FACT-Finder configuration.');
		}
	}
	
	/**
	 * Loads previously registered failed attempts from cache, if they exist.
	 * Returns an empty array, otherwise.
	 *
	 * @return	array of int
	 **/
	protected function _loadFailedAttempts()
	{
		$cachedContent = Mage::app()->loadCache($this->_getCacheId());
		$failedAttempts = array();
		if($cachedContent)
			$failedAttempts = unserialize($cachedContent);
		
		return $failedAttempts;
	}
	
	/**
	 * Save failed attempts to cache.
	 *
	 * @param	array of int	failed attempts
	 **/
	public function saveFailedAttempts($failedAttempts)
	{
		Mage::app()->saveCache(serialize($failedAttempts), $this->_getCacheId(), array(self::CACHE_TAG));
	}
	
	/**
	 * Removes entries from a list of minute-timestamps which are older than a given delay set within the configuration
	 *
	 * @param	array of int	entries
	 **/
	protected function _removeOldEntries($entries)
	{
		$delay = Mage::getStoreConfig('factfinder/fallback/wait_time');
		$newEntries = array();
		
		foreach($entries as $entry)
		{
			if(intval(time() / 60) - $entry < $delay)
				$newEntries[] = $entry;
		}
		
		return $newEntries;
	}

    /**
     * get Module Status depending on Module
     *
     * @return boolean
     */
    public function getIsOnSearchPage()
    {
        return Mage::app()->getRequest()->getModuleName() == 'catalogsearch' || Mage::app()->getRequest()->getModuleName() == 'xmlconnect';
    }


    /**
     * get Toolbar Block
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    protected function _getToolbarBlock()
    {
        $mainBlock = Mage::app()->getLayout()->getBlock('search.result');
        if($mainBlock instanceof Mage_CatalogSearch_Block_Result){
            $toolbarBlock = $mainBlock->getListBlock()->getToolbarBlock();
        }else{
            $toolbarBlock = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar');
        }

        return $toolbarBlock;
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
     * get Entity ID Field Name by Configuration or via Entity
     *
     * @return string
     */
    public function getIdFieldName()
    {
        $idFieldName = Mage::getStoreConfig(self::XML_CONFIG_PATH_PRODUCT_IDENTIFIER);
        if(!$idFieldName){
            $idFieldName = $this->getEntity()->getIdFieldName();
        }
        return $idFieldName;
    }

    /**
     * get FACT-Finder Suggest URL
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        
        if (Mage::getStoreConfig(self::XML_CONFIG_PATH_USE_PROXY)) {
            $params = array();
            if (Mage::app()->getStore()->isCurrentlySecure()) {
                $params['_secure'] = true;
            }
            $url = $this->_getUrl('factfinder/proxy/suggest', $params);
        } else {
			$url = Mage::getSingleton('factfinder/adapter')->getSuggestUrl(); 
		}
        return $url;
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
     * get Page Limit
     *
     * @return int
     */
    public function getPageLimit()
    {
        $limit = $this->_getToolbarBlock()->getLimit();
        if ($limit == 'all') {
            $limit = 2*3*4*5*6; //a lot of products working for each layout
        }
        return $limit;
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
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        return Mage::helper('catalogsearch')->getQuery();
    }

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
     * return product campaings
     *
     * @param array $productIds
     * @return FACTFinder_CampaignIterator
     */
    public function getProductCampaigns($productIds)
    {
        try {
            // get productcampaign adapter and set product id or sku array
            $productCampaignAdapter = Mage::getModel('factfinder/adapter')->getProductCampaignAdapter();
            $productCampaignAdapter->setProductIds($productIds);
            $productCampaignAdapter->makeProductCampaign();

            return $productCampaignAdapter->getCampaigns();
        } catch(Exception $e) {
			// TODO: log exception
            return array();
        }
    }	 
}