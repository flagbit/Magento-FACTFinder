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
 * Search Collection with FACT-Finder Search Results 
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_Mysql4_Search_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    const CACHE_TAG  = 'FACTFINDER';
	const CACHE_ID = "FallbackCache";
    const REQUEST_ID_PREFIX = 'FACTFINDER_';
	
	protected $_failedAttemptRegistered = false;
	
    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
		if($this->_skipFactFinder())
		{
			parent::_loadEntities($printQuery, $logQuery);
			return $this;
		}
		
		$count = $this->_getAdapter()->getSearchResultCount();
		
		if(!$count && $this->_useFallback())
		{
			$this->_registerFailedAttempt();
			$count = parent::getSize();
		}
		
		return $count;
    }
    
    /**
     * get Factfinder Search Adapter
     * 
     * @return Flagbit_FactFinder_Model_Adapter
     */
    protected function _getAdapter()
    {
    	return Mage::getSingleton('factfinder/adapter');	
    }
    
    /**
     * Load entities records into items
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
		if($this->_skipFactFinder())
		{
			parent::_loadEntities($printQuery, $logQuery);
			return $this;
		}
		// get product Ids from Fact-Finder
    	$productIds = $this->_getAdapter()->getSearchResultProductIds();
		
		if(!count($productIds) && $this->_useFallback())
		{
			$this->_registerFailedAttempt();
			parent::_loadEntities($printQuery, $logQuery);
			return $this;
		}
		
		$idFieldName = Mage::helper('factfinder/search')->getIdFieldName();

        if (!empty($productIds)) {

        	// add Filter to Query
        	$this->addFieldToFilter(
        		$idFieldName,
        		array('in'=>array_keys($productIds))
        	);
 
	        $this->_pageSize = null;      
	        $entity = $this->getEntity();
	        
			$this->getSelect()->reset(Zend_Db_Select::LIMIT_COUNT);
           	$this->getSelect()->reset(Zend_Db_Select::LIMIT_OFFSET);	        
	
	        $this->printLogQuery($printQuery, $logQuery);
	        Mage::helper('factfinder/debug')->log('Search SQL Query: '.$this->getSelect()->__toString());
	
	        try {
	            $rows = $this->_fetchAll($this->getSelect());
	        } catch (Exception $e) {
	            Mage::printException($e, $this->getSelect());
	            $this->printLogQuery(true, true, $this->getSelect());
	            throw $e;
	        }
	
	        $items = array();
	        foreach ($rows as $v) {        	
				$items[trim($v[$idFieldName])] = $v;
	        }

	        foreach ($productIds as $productId => $additionalData){
	        	
	        	if(empty($items[$productId])){
	        		continue;
	        	}
	        	$v = array_merge($items[$productId], $additionalData->toArray());
	            $object = $this->getNewEmptyItem()
	                ->setData($v);
  
	            $this->addItem($object);
	            if (isset($this->_itemsById[$object->getId()])) {
	                $this->_itemsById[$object->getId()][] = $object;
	            }
	            else {
	                $this->_itemsById[$object->getId()] = array($object);
	            }        	
	        }
	        
        }
        return $this;
    }      

    /**
     * Add search query filter
     *
     * @param   Mage_CatalogSearch_Model_Query $query
     * @return  Mage_CatalogSearch_Model_Mysql4_Search_Collection
     */
    public function addSearchFilter($query)
    {
        return $this;
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
     */
    public function setOrder($attribute, $dir='desc')
    {
        return $this;
    }
	
	/**
	 * Determines whether the fallback should be used
	 *
	 * @return	bool
	 **/
	protected function _useFallback()
	{
		return Mage::getStoreConfig('factfinder/fallback/use_fallback');
	}
	
	/**
	 * Determines whether FACT-Finder should be skipped completely, because it has failed to respond too often
	 *
	 * @return bool
	 **/
	protected function _skipFactFinder()
	{
		if(!$this->useFallback)
			return false;
		
		$failedAttempts = $this->_loadFailedAttempts();
		
		$delay = Mage::getStoreConfig('factfinder/fallback/wait_time');
		$skip = false;
		$newFailedAttempts = array();
		
		foreach($failedAttempts as $attempt)
		{
			if(intval(time() / 60) - $attempt < $delay)
			{
				$newFailedAttempts[] = $attempt;
				$skip = true;
			}
		}
		
		Mage::app()->saveCache(serialize($newFailedAttempts), $this->_getCacheId(), array(self::CACHE_TAG));
		return $skip;
	}
	
	protected function _getCacheId()
	{
		return self::REQUEST_ID_PREFIX . self::CACHE_ID;
	}
	
	/**
	 * Registers that FACT-Finder has failed to respond.
	 * Only one failed attempt per lifetime of this object will be registered.
	 **/
	protected function _registerFailedAttempt()
	{
		if($this->_failedAttemptRegistered)
			return;
			
		$failedAttempts = $this->_loadFailedAttempts();
		
		$failedAttempts[] = intval(time() / 60);
		
		Mage::app()->saveCache(serialize($failedAttempts), $this->_getCacheId(), array(self::CACHE_TAG));
		
		$this->_failedAttemptRegistered = true;
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
}
