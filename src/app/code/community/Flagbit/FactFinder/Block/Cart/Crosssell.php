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
    protected $_recommendationsHandler;

    protected function _prepareLayout()
    {
        if(Mage::getStoreConfigFlag('factfinder/activation/crosssell')){
            $productIds = $this->_getCartProductIds();
            $this->_recommendationsHandler = Mage::getSingleton('factfinder/handler_recommendations', $productIds);
        }
        return parent::_prepareLayout();
    }

    /**
     * Overwritten function. Gets information from FACT-Finder Collection instead of product link collection.
     */
    public function getItems()
    {
        if (!Mage::getStoreConfigFlag('factfinder/activation/crosssell')) {
            return parent::getItems();
        }

        $items = $this->getData('items');
        if (is_null($items)) {
            $items = array();
            $ninProductIds = $this->_getCartProductIds();
            if ($ninProductIds) {
                $collection = $this->_getCollection()
                    ->setRecommendations($this->_recommendationsHandler->getRecommendations());
                if (!empty($ninProductIds)) {
                    // Before FF 6.7 only one product id will be considered.
                    // In that case (only) it could happen that another product in the cart is among the
                    // recommendations.
                    // TODO: This filter does not seem to work.
                    $collection->addExcludeProductFilter($ninProductIds);
                }

                foreach ($collection as $item) {
                    $items[] = $item;
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
        if (!Mage::getStoreConfigFlag('factfinder/activation/crosssell')) {
            return parent::_getCollection();
        }

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

    /**
     * Get ids of products that are in cart
     *
     * @return array
     */
    protected function _getCartProductIds()
    {
        $ids = $this->getData('_cart_product_ids');
        if (is_null($ids)) {
            $ids = array();
            foreach ($this->getQuote()->getAllItems() as $item) {
                if ($product = $item->getProduct()) {
                    $ids[] = $product->getData(Mage::helper('factfinder/search')->getIdFieldName());
                }
            }
            $this->setData('_cart_product_ids', $ids);
        }
        return $ids;
    }


    /**
     * @param int $count
     *
     * @return $this
     */
    public function setMaxItemCount($count)
    {
        $this->_maxItemCount = $count;

        return $this;
    }


}