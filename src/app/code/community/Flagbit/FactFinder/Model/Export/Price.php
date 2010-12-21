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
 * This helper class provides the Price export
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_Export_Price extends Mage_Core_Model_Mysql4_Abstract {
	
	/**
	 * defines Export Columns
	 * @var array
	 */
	protected $_exportColumns = array('entity_id', 'customer_group_id', 'final_price', 'min_price');	
	
    /**
     * Resource initialization
     */
    protected function _construct(){
    	$this->_setResource('core');
    }	
	
    /**
     * add CSV Row
     * 
     * @param array $data
     */
    protected function _addCsvRow($data)
    {   
    	foreach($data as &$item){
    		$item = str_replace(array("\r", "\n", "\""), ' ', addcslashes(strip_tags($item), '"'));
    	}

    	echo '"'.implode('";"', $data).'"'."\n"; 
    }		
	
	 /**
     * export Product Prices
     * direct Output as CSV
     *
     * @param int $storeId Store View Id
     */
    public function doExport($storeId = null)
    {
    	$this->_addCsvRow($this->_exportColumns);
        for($i=1; $prices = $this->_getPrices($storeId, $i); $i++){
            foreach($prices as $price){
            	$this->_addCsvRow($price);
            }
        }    	
    }

	/**
	 * get Prices from Price Index Table
	 * 
	 * @param int $storeId Store ID
	 * @param int $part 
	 * @param int $limit
	 * @return array
	 */
    protected function _getPrices($storeId, $part = 1, $limit = 100){

        $store  = Mage::app()->getStore($storeId);
        $select = $this->_getWriteAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog/product_index_price')),
                $this->_exportColumns);

        if($storeId !== null){
        	$select->where('e.website_id = ?', $store->getWebsiteId());
        }                
                
        $select->limitPage($part, $limit)
            ->order('e.entity_id');
  
        return $this->_getWriteAdapter()->fetchAll($select);    	
    }
}
