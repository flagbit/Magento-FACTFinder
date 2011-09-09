<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Overwritten block.
 *
 * Replaces the crosssell block within cart view. Gets data from FACT-Finder instead of product link collection.
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Michael Türk <tuerk@flagbit.de>
 * @version   $Id: Processor.php 647 2011-03-21 10:32:14Z rudolf_batt $
 */
class Flagbit_FactFinder_Block_Cart_Crosssell extends Mage_Checkout_Block_Cart_Crosssell
{
    /**
     * Overwritten function. Gets information from FACT-Finder Collection instead of product link collection.
     */
    public function getItems()
    {
        $items = $this->getData('items');
        if (is_null($items)) {
            $items = array();
            $ninProductIds = $this->_getCartProductIds();
            if ($ninProductIds) {
                $lastAdded = (int) $this->_getLastAddedProductId();
                if ($lastAdded) {
                    $searchHelper = Mage::helper('factfinder/search');
                    $idFieldName = $searchHelper->getIdFieldName();

                    $recommendationAdapter = Mage::getModel('factfinder/adapter')->getRecommendationAdapter();
                    $product = Mage::getModel('catalog/product')->load($lastAdded);

                    $collection = $this->_getCollection()
                        ->setRecommendations($recommendationAdapter->getRecommendations($product->getData($idFieldName)));
                    if (!empty($ninProductIds)) {
                        $collection->addExcludeProductFilter($ninProductIds);
                    }

                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }

            }

            $this->setData('items', $items);
        }
        return $items;
    }

    /**
     * Get crosssell products collection. Get it from product recommendation collection for communication with FACT-Finder.
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
     */
    protected function _getCollection()
    {
        $collection = Mage::getResourceModel('factfinder/product_recommendation_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addStoreFilter()
            ->setPageSize($this->_maxItemCount);
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }
}