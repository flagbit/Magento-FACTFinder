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
        if (!Mage::getStoreConfigFlag('factfinder/activation/crosssell')) {
            return parent::getItems();
        }

        $items = $this->getData('items');
        if (is_null($items)) {
            try {
                $items = array();
                $ninProductIds = $this->_getCartProductIds();
                if ($ninProductIds) {
                    $lastAdded = (int) $this->_getLastAddedProductId();
                    if ($lastAdded) {
                        $searchHelper = Mage::helper('factfinder/search');
                        $idFieldName = $searchHelper->getIdFieldName();

                        $recommendationAdapter = Mage::getModel('factfinder/adapter')->getRecommendationAdapter();
                        $attributeValue = Mage::getModel('catalog/product')->getResource()->getAttributeRawValue($lastAdded, $idFieldName, Mage::app()->getStore()->getId());

                        $collection = $this->_getCollection()
                            ->setRecommendations($recommendationAdapter->getRecommendations($attributeValue));
                        if (!empty($ninProductIds)) {
                            $collection->addExcludeProductFilter($ninProductIds);
                        }

                        foreach ($collection as $item) {
                            $items[] = $item;
                        }
                    }

                }
            }
            catch (Exception $e) {
                Mage::logException($e);
                $items = array();
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
}