<?php
/**
 * FACTFinder_Recommendation
 *
 * @category Mage
 * @package FACTFinder_Recommendation
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Class FACTFinder_Recommendation_Model_Observer
 *
 * @category Mage
 * @package FACTFinder_Recommendation
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Recommendation_Model_Observer
{


    /**
     * Loads recommended items within upsell\cross-sell collection
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function loadRecommendedItemsItems($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled('recommendation')) {
            return;
        }

        $collection = $observer->getCollection();

        $availableTypes = array(
            Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL,
            Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL,
        );
        if (!$collection instanceof Mage_Catalog_Model_Resource_Product_Link_Product_Collection
            || !in_array($collection->getLinkModel()->getLinkTypeId(), $availableTypes)
        ) {
            return;
        }

        $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();

        $ids = $this->_getProductIds($collection, $idFieldName);
        $this->_processCollection($collection, $ids, $idFieldName);

    }


    /**
     * Remove all not needed and add all the necessary filter to the product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Link_Product_Collection $collection
     * @param array                                                       $productIds
     * @param string                                                      $idFieldName
     *
     * @return FACTFinder_Recommendation_Model_Observer
     */
    protected function _processCollection($collection, $productIds, $idFieldName)
    {
        $select = $collection->getSelect();

        $linkParts = array('links', 'link_attribute_position_int');

        // remove link parts from from
        $from = $select->getPart(Zend_Db_Select::FROM);
        foreach ($linkParts as $linkPart) {
            unset($from[$linkPart]);
        }

        $select->setPart(Zend_Db_Select::FROM, $from);

        // reset all where conditions
        $select->reset(Zend_Db_Select::WHERE);

        // remove link part from columns
        $columns = $select->getPart(Zend_Db_Select::COLUMNS);
        foreach ($columns as $index => $column) {
            if (in_array($column[0], $linkParts)) {
                unset($columns[$index]);
            }
        }

        $select->setPart(Zend_Db_Select::COLUMNS, $columns);

        $handler = Mage::getModel('factfinder_recommendation/handler_recommendations', $productIds);
        $recommendations = $handler->getRecommendedIds();

        if ($recommendations) {
            $collection->addAttributeToFilter($idFieldName, array('in' => $recommendations));

            $order = new Zend_Db_Expr($collection->getConnection()->quoteInto(
                'find_in_set(`e`.`' . $idFieldName . '`, ?)',
                implode(',', $recommendations)
            ));
            $select->order($order);
        } else {
            // do not load anything
            $collection->addAttributeToFilter($idFieldName, array('in' => array(-1)));
        }

        Mage::register('recommendation_collection', $collection, true);

        return $this;
    }


    /**
     * Get current product ids
     *
     * @param Mage_Catalog_Model_Resource_Product_Link_Product_Collection $collection
     * @param string                                                      $idFieldName
     *
     * @return array
     */
    protected function _getProductIds($collection, $idFieldName)
    {
        if ($collection->getProduct() && $collection->getProduct()->getId()) {
            return array($collection->getProduct()->getData($idFieldName));
        }

        $ids = array();

        $product = Mage::registry('product');
        if ($product instanceof Mage_Catalog_Model_Product) {
            $ids[] = $product->getData($idFieldName);
        }

        if ($collection->getLinkModel()->getLinkTypeId() == Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            foreach ($quote->getAllItems() as $item) {
                if ($product = $item->getProduct()) {
                    $ids[] = $product->getData($idFieldName);
                }
            }
        }

        return $ids;
    }


    /**
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function triggerImportAfterExport($observer)
    {
        $storeId = $observer->getStoreId();
        $helper = Mage::helper('factfinder_recommendation');
        if ($helper->shouldTriggerImport($storeId)) {
            $helper->triggerImport($storeId);
        }
    }


}
