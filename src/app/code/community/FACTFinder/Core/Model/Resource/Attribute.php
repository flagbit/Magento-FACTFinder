<?php
class FACTFinder_Core_Model_Resource_Attribute extends Mage_CatalogSearch_Model_Resource_Fulltext
{

    protected function _construct()
    {
        $this->_init('catalog/attribute', 'entity_id');
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return Mage_Eav_Model_Config
     */
    public function getEavConfig()
    {
        return Mage::getSingleton('eav/config');
    }

    /**
     * Retrieve Searchable attributes
     *
     * @param int    $storeId
     *
     * @return array
     */
    public function getSearchableAttributes($storeId = null)
    {
        if ($this->_searchableAttributes === null) {
            $this->_searchableAttributes = array();
            $entityType = $this->getEavConfig()->getEntityType('catalog_product');
            $entity = $entityType->getEntity();

            $userDefinedAttributes = Mage::getStoreConfig('factfinder/export/attributes', $storeId);
            $userDefinedAttributes = array_keys(
                Mage::helper('factfinder/backend')->unserializeFieldValue($userDefinedAttributes)
            );

            $whereCond = array(
                $this->_getWriteAdapter()->quoteInto('additional_table.is_searchable=?', 1),
                $this->_getWriteAdapter()->quoteInto('additional_table.is_filterable=?', 1),
                $this->_getWriteAdapter()->quoteInto('additional_table.used_for_sort_by=?', 1),
                $this->_getWriteAdapter()->quoteInto(
                    'main_table.attribute_code IN(?)',
                    array_merge(array('status', 'visibility'), $userDefinedAttributes)
                )
            );

            $select = $this->_getWriteAdapter()->select()
                ->from(array('main_table' => $this->getTable('eav/attribute')))
                ->join(
                    array('additional_table' => $this->getTable('catalog/eav_attribute')),
                    'additional_table.attribute_id = main_table.attribute_id'
                )
                ->where('main_table.entity_type_id=?', $entityType->getEntityTypeId())
                ->where(join(' OR ', $whereCond))
                ->order('main_table.attribute_id', 'asc');

            $attributesData = $this->_getWriteAdapter()->fetchAll($select);
            $this->getEavConfig()->importAttributesData($entityType, $attributesData);

            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $attribute = $this->getEavConfig()->getAttribute($entityType, $attributeCode);
                $attribute->setEntity($entity);
                $this->_searchableAttributes[$attribute->getId()] = $attribute;
            }
        }

        return $this->_searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     *
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getSearchableAttribute($attribute)
    {
        return $this->_getSearchableAttribute($attribute);
    }


    /**
     * Load product(s) attributes
     *
     * @param int   $storeId
     * @param array $productIds
     * @param array $attributeTypes
     *
     * @return array
     */
    public function getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        return $this->_getProductAttributes($storeId, $productIds, $attributeTypes);
    }


}