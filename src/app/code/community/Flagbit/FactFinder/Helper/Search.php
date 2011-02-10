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
	 * if FACT-Finder enabled?
	 * 
	 * @return boolean
	 */
	public function getIsEnabled($searchPageCheck = true)
	{
		return (
					Mage::getStoreConfig('factfinder/search/enabled') 
					&& !Mage::getStoreConfig('advanced/modules_disable_output/Flagbit_FactFinder')
					&& ($searchPageCheck == false || $this->getIsOnSearchPage())
				) ? true : false;
	}
	
	public function getIsOnSearchPage()
	{
		return Mage::app()->getRequest()->getModuleName() == 'catalogsearch';	
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
     * get FACT-Finder Suggest URL
     * 
     * @return string
     */
    public function getSuggestUrl()
    {
    	return Mage::getSingleton('factfinder/adapter')->getSuggestUrl();
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
    	return $this->_getToolbarBlock()->getLimit();	
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
}