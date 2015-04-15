<?php

class FACTFinder_Core_Model_Resource_Search_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_getSearchHandler()->getSearchResultCount();
    }


    /**
     * Get FACT-Finder Facade
     *
     * @return FACTFinder_Core_Model_Handler_Search
     */
    protected function _getSearchHandler()
    {
        return Mage::getSingleton('factfinder/handler_search');
    }


    /**
     * Add search query filter
     *
     * @param   Mage_CatalogSearch_Model_Query $query
     *
     * @return  FACTFinder_Core_Model_Resource_Search_Collection
     */
    public function addSearchFilter($query)
    {
        return $this;
    }


    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     *
     * @return FACTFinder_Core_Model_Resource_Search_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        return $this;
    }


    /**
     * Load entities records into items
     *
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @throws Exception
     * @throws Mage_Core_Exception
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        // get product IDs from Fact-Finder
        $productIds = $this->_getSearchHandler()->getSearchResult();

        $facade = Mage::getSingleton('factfinder/facade');
        $refKey = $facade->getSearchResult()->getRefKey();
        if ($refKey) {
            Mage::getSingleton('core/session')->setFactFinderRefKey($refKey);
        }

        if (!empty($productIds)) {
            $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();

            // add Filter to Query
            $this->addFieldToFilter(
                $idFieldName,
                array('in' => array_keys($productIds))
            );

            $this->_pageSize = null;

            $this->getSelect()->reset(Zend_Db_Select::LIMIT_COUNT);
            $this->getSelect()->reset(Zend_Db_Select::LIMIT_OFFSET);

            $this->printLogQuery($printQuery, $logQuery);
            Mage::helper('factfinder/debug')->log('Search SQL Query: ' . $this->getSelect()->__toString());

            try {
                $rows = $this->_fetchAll($this->getSelect());
            } catch (Exception $e) {
                Mage::printException($e, $this->getSelect());
                $this->printLogQuery(true, true, $this->getSelect());
                throw $e;
            }

            $items = array();
            foreach ($rows as $v) {
                $items[trim($v[$idFieldName])] = $v;
            }

            foreach ($productIds as $productId => $additionalData) {

                if (empty($items[$productId])) {
                    continue;
                }
                $v = array_merge($items[$productId], $additionalData->toArray());
                $object = $this->getNewEmptyItem()
                    ->setData($v);

                $this->addItem($object);
                if (isset($this->_itemsById[$object->getId()])) {
                    $this->_itemsById[$object->getId()][] = $object;
                } else {
                    $this->_itemsById[$object->getId()] = array($object);
                }
            }

        }

        return $this;
    }

}