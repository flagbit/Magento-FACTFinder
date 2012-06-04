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
    
    /**
     * if FACT-Finder enabled?
     * 
     * @return boolean
     */
    public function getIsEnabled($searchPageCheck = true, $functionality = '')
    {
        if (!Mage::getStoreConfigFlag('factfinder/search/enabled')
            || Mage::getStoreConfigFlag('advanced/modules_disable_output/Flagbit_FactFinder')
            || ($searchPageCheck == true && !$this->getIsOnSearchPage() && !Mage::getStoreConfigFlag('factfinder/activation/navigation'))) {
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
        $url = Mage::getSingleton('factfinder/adapter')->getSuggestUrl();
        if(Mage::getStoreConfig(self::XML_CONFIG_PATH_USE_PROXY)){
            $url = $this->_getUrl('factfinder/proxy/suggest');
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
        // get productcampaign adapter and set product id or sku array
        $productCampaignAdapter = Mage::getModel('factfinder/adapter')->getProductCampaignAdapter();
        $productCampaignAdapter->setProductIds($productIds);
        $productCampaignAdapter->makeProductCampaign();
        
        return $productCampaignAdapter->getCampaigns();
    }
}