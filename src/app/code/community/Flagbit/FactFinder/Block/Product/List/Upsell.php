<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Block class for upselling
 *
 * Rewritten block - data is now caught by FACT-Finder, passed to normal collection, works quite as if it was the
 * default behavior.
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Michael Türk <türk@flagbit.de>
 * @version   $Id: Search.php 678 2011-08-01 13:02:50Z rudolf_batt $
 */
class Flagbit_FactFinder_Block_Product_List_Upsell extends Mage_Catalog_Block_Product_List_Upsell
{
	/**
	 * Method overwritten. Data is not read from product link collection but from FACT-Finder interface instead.
	 */
    protected function _prepareData()
    {

        if (!Mage::getStoreConfigFlag('factfinder/activation/upsell')) {
            return parent::_prepareData();
        }

        try {
	        $product = Mage::registry('product');
	        /* @var $product Mage_Catalog_Model_Product */

	        $searchHelper = Mage::helper('factfinder/search');
	        $idFieldName = $searchHelper->getIdFieldName();

	        $recommendationAdapter = Mage::getModel('factfinder/adapter')->getRecommendationAdapter();
	        $recommendations = $recommendationAdapter->getRecommendations($product->getData($idFieldName));

	        if ($recommendations == null) {
	            throw new Exception('No recommendations given - check connection to FACT-Finder and FACT-Finder configuration');
	        }
	        elseif ($recommendations->getFoundRecordsCount() == 0) {
	            $this->_itemCollection = new Varien_Data_Collection();
	            return $this;
	        }

	        $this->_itemCollection = Mage::getResourceModel('factfinder/product_recommendation_collection')
	        ->addStoreFilter()
	        ;

	        if ($this->getItemLimit('upsell') > 0) {
	            $this->_itemCollection->setPageSize($this->getItemLimit('upsell'));
	        }
	        $this->_itemCollection->setRecommendations($recommendations);

	        Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
	            Mage::getSingleton('checkout/session')->getQuoteId()
	        );
	        $this->_addProductAttributesAndPrices($this->_itemCollection);

// 	//        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
	        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);
// 	        Zend_Debug::dump($this->_itemCollection->getSelect()->__toString());
// 	        Zend_Debug::dump($recommendations);
// 	        die();
	        $this->_itemCollection->load();

	        /**
	         * Updating collection with desired items
	         */
	        Mage::dispatchEvent('catalog_product_upsell', array(
	            'product'       => $product,
	            'collection'    => $this->_itemCollection,
	            'limit'         => $this->getItemLimit()
	        ));

	        foreach ($this->_itemCollection as $product) {
	            $product->setDoNotUseCategoryId(true);
	        }
        }
        catch (Exception $e) {
        	Mage::logException($e);
        	$this->_itemCollection = new Varien_Data_Collection();
        }

        return $this;
    }
}