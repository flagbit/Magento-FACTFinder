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
 * This helper class provides the Stock export
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: Order.php 610 2011-01-19 14:13:34Z weller $
 */
class Flagbit_FactFinder_Model_Export_Order extends Mage_Core_Model_Mysql4_Abstract {
	
	/**
	 * defines order-qty column
	 * @var string
	 */
	protected $_orderQtyColumn = 'ordered_qty';
	
	/**
	 * defines Export Columns
	 * @var array
	 */
	protected $_exportColumns = array('entity_id', 'sku');
	
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
    	foreach($data as $key => &$item){
    		$item = str_replace(array("\r", "\n", "\""), ' ', addcslashes(strip_tags($item), '"'));
    	}

    	echo '"'.implode('";"', $data).'"'."\n"; 
    }		
	
	 /**
     * export Order Data
     * direct Output as CSV
     *
     * @param int $storeId Store Id
     */
    public function doExport($storeId = null)
    {
		$csvHeadline = array($this->_orderQtyColumn);
		foreach($this->_exportColumns as $col) {
			$csvHeadline[] = $col;
		}
    	$this->_addCsvRow($csvHeadline);
		
        for($i=1; $orders = $this->_getOrderData($storeId, $i); $i++){
            foreach($orders as $order){
            	$this->_addCsvRow($order);
            }
        }    	
    }

	/**
	 * get Orders from Order Index Table
	 * 
	 * @param int $storeId Store ID
	 * @param int $part 
	 * @param int $limit
	 * @return array
	 */
    protected function _getOrderData($storeId, $part = 1, $limit = 100)
	{
		$reportsProductCollection = Mage::getResourceModel('reports/product_collection');
		$qtyOrderedTableName = $this->getTable('sales/order_item');
        
		$qtyOrderedFieldName = 'qty_ordered';
        $productIdFieldName = 'product_id';
		
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $reportsProductCollection->getConnection()->quoteInto(' AND (e.type_id NOT IN (?))', $compositeTypeIds);

         $_joinCondition = $reportsProductCollection->getConnection()->quoteInto(
                'order.entity_id = order_items.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
         );
		 
        $select = $this->_getWriteAdapter()->select();
		$select
			->from(
				array('order_items' => $qtyOrderedTableName),
				array($this->_orderQtyColumn => "SUM(order_items.{$qtyOrderedFieldName})"))
			->joinInner(
				array('order' => $this->getTable('sales/order')),
				$_joinCondition,
				array())
			->joinInner(
				array('e' => $reportsProductCollection->getProductEntityTableName()),
                "e.entity_id = order_items.{$productIdFieldName} AND e.entity_type_id = {$reportsProductCollection->getProductEntityTypeId()}{$productTypes}",
				$this->_exportColumns)
            ->group('e.entity_id')
            ->having($this->_orderQtyColumn.' > 0')
			->limitPage($part, $limit);
		
        return $this->_getWriteAdapter()->fetchAll($select);    	
    }
}
