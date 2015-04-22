<?php
class FACTFinder_Recommendation_Model_Observer
{

    /**
     * Loads recommended items within upsell\cross-sell collection
     *
     * @param $observer
     */
    public function loadLoadRecommendedItemsItems($observer)
    {
        $collection = $observer->getCollection();
        if (!$collection instanceof Mage_Catalog_Model_Resource_Product_Link_Product_Collection
            || ($collection->getLinkModel()->getLinkTypeId() !== Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL
                && $collection->getLinkModel()->getLinkTypeId() !== Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL
            )
        ) {
            return;
        }

        $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();

        $ids = $this->_getProductIds($idFieldName);
        $this->_processCollection($collection, $ids, $idFieldName);

    }


    /**
     * Remove all not needed and add all the necessary filter to the product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Link_Product_Collection $collection
     * @param array $productIds
     * @param string $idFieldName
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
    }


    /**
     * Get current product ids
     *
     * @param $idFieldName
     *
     * @return array
     */
    protected function _getProductIds($idFieldName)
    {
        $ids = array();

        $product = Mage::registry('product');
        if ($product instanceof Mage_Catalog_Model_Product) {
            $ids[] = $product->getData($idFieldName);
        }

        return $ids;
    }


}