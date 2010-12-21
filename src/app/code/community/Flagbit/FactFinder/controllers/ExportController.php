<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Controller class
 * 
 * This class the Export Controller
 * It provides a Products, Prices and Stocks Export
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: AsnFilterItem.php 25893 2010-06-29 08:19:43Z rb $
 */
class Flagbit_FactFinder_ExportController extends Mage_Core_Controller_Front_Action {
	
	/**
	 * get current Store ID
	 * 
	 * @return int
	 */
	protected function _getStoreId()
	{
		return Mage::app()->getStore()->getId();
	}
	
    /**
     * Initialize Product Export 
     */
	public function productAction()
	{		
		$exportModel = Mage::getModel('factfinder/export_product');
		$exportModel->doExport(
			$this->_getStoreId()
		);	
	}
	
    /**
     * Initialize Price Export 
     */	
	public function priceAction()
	{	
		$exportModel = Mage::getModel('factfinder/export_price');
		$exportModel->doExport(
			$this->_getStoreId()
		);		
	}

    /**
     * Initialize Stock Export 
     */	
	public function stockAction()
	{	
		$exportModel = Mage::getModel('factfinder/export_stock');
		$exportModel->doExport(
			$this->_getStoreId()
		);		
	}	
}