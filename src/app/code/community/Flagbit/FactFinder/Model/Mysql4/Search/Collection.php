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
    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
    	return $this->_getAdapter()->getSearchResultCount();
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

		// get product Ids from Fact-Finder
    	$productIds = $this->_getAdapter()->getSearchResultProductIds();
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
}
