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
 * Filter Attribute Collection 
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_Mysql4_Product_Attribute_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection {
	
	protected $_result = null;
	protected $_attributeLabels = null;
	protected $_storeId = null;
    
    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
    	return count($this->_getAdapter()->getAfterSearchNavigation());
    }
    
    /**
     * get Adapter
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
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }    	

    	$result = $this->_getAdapter()->getAfterSearchNavigation();

        if (count($result)) {
	        $this->resetData();
	        
            foreach ($result as $row) {
                $item = $this->getNewEmptyItem();
                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $row['store_label'] = $this->_getStoreLabelsByAttributeCode($row['name']);
                $item->addData($row);
                $this->addItem($item);
            }

	        $this->_setIsLoaded();
	        $this->_afterLoad();  
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
     * Retrieve store labels by given attribute code
     *
     * @param string $attributeCode
     * @return array
     */
    protected function _getStoreLabelsByAttributeCode($attributeCode)
    {
		if($this->_attributeLabels === null){
			$entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');  	
	    	
	        $values = array();
	
	        $select = $this->getConnection()->select()        
	                ->from(array('main_table' => $this->getTable('eav/attribute')), array('attribute_code'))
	                ->joinLeft(
	                    array('additional_table' => $this->getTable('eav/attribute_label')),
	                    'additional_table.attribute_id = main_table.attribute_id',
	                    null
	                )
					->columns(array('value' => new Zend_Db_Expr('IF(additional_table.value IS NULL, main_table.frontend_label, additional_table.value)')))		                
	                ->where('main_table.entity_type_id = ?', $entityType->getEntityTypeId())
	                ->where('additional_table.store_id IS NULL OR additional_table.store_id=?', $this->_storeId);     
	        
			$this->_attributeLabels = $this->getConnection()->fetchPairs($select);            
		}
		return isset($this->_attributeLabels[$attributeCode]) ? $this->_attributeLabels[$attributeCode] : $attributeCode;
    }    
    
    /**
     * set Store ID
     * 
     * @param int $storeId
     * @return Flagbit_FactFinder_Model_Mysql4_Product_Attribute_Collection
     */
    public function setStoreId($storeId)
    {
    	$this->_storeId = $storeId;
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