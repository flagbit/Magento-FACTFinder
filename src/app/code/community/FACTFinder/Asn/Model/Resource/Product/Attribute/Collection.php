<?php

/**
 * Model class
 *
 * Filter Attribute Collection
 *
 */
class FACTFinder_Asn_Model_Resource_Product_Attribute_Collection
    extends Mage_Catalog_Model_Resource_Product_Attribute_Collection
{
    /**
     * @var array|null
     */
    protected $_attributeLabels = null;

    /**
     * @var int|null
     */
    protected $_storeId = null;


    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        return count($this->_getSearchHandler()->getAfterSearchNavigation());
    }


    /**
     * Get search handler
     *
     * @return FACTFinder_Asn_Model_Handler_Search
     */
    protected function _getSearchHandler()
    {
        return Mage::getSingleton('factfinder_asn/handler_search');
    }


    /**
     * Load entities records into items
     *
     * @return FACTFinder_Asn_Model_Resource_Product_Attribute_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $result = $this->_getSearchHandler()->getAfterSearchNavigation();

        if (count($result)) {
            $this->resetData();

            foreach ($result as $row) {
                $item = $this->getNewEmptyItem();
                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $row['store_label'] = $this->_getStoreLabelsByAttributeCode($row['name']);
                $item->addData($row);
                $this->addItem($item);
            }

            $this->_setIsLoaded();
            $this->_afterLoad();
        }

        return $this;
    }


    /**
     * Add search query filter
     *
     * @param   Mage_CatalogSearch_Model_Query $query
     *
     * @return  Mage_CatalogSearch_Model_Mysql4_Search_Collection
     */
    public function addSearchFilter($query)
    {
        return $this;
    }


    /**
     * Retrieve store labels by given attribute code
     *
     * @param string $attributeCode
     *
     * @return array|string
     */
    protected function _getStoreLabelsByAttributeCode($attributeCode)
    {
        if ($this->_attributeLabels === null) {
            $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');

            $select = $this->getConnection()->select()
                ->from(array('main_table' => $this->getTable('eav/attribute')), array('attribute_code'))
                ->joinLeft(
                    array('additional_table' => $this->getTable('eav/attribute_label')),
                    'additional_table.attribute_id = main_table.attribute_id',
                    null
                )
                ->columns(array(
                    'value' => new Zend_Db_Expr('IF(additional_table.value IS NULL, main_table.frontend_label, additional_table.value)')
                ))
                ->where('main_table.entity_type_id = ?', $entityType->getEntityTypeId())
                ->where('additional_table.store_id IS NULL OR additional_table.store_id=?', $this->_storeId);

            $this->_attributeLabels = $this->getConnection()->fetchPairs($select);
        }

        if (!isset($this->_attributeLabels[$attributeCode])) {
            return  $attributeCode;
        }

        return $this->_attributeLabels[$attributeCode];
    }


    /**
     * Set Store ID
     *
     * @param int $storeId
     *
     * @return FACTFinder_Asn_Model_Resource_Product_Attribute_Collection
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }


    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     *
     * @return FACTFinder_Asn_Model_Resource_Product_Attribute_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        return $this;
    }


}
