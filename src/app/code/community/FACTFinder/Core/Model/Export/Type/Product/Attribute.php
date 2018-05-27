<?php

/**
 * Class FACTFinder_Core_Model_Export_Type_Product_Attribute
 *
 * @method FACTFinder_Core_Model_Resource_Attribute getResource()
 */
class FACTFinder_Core_Model_Export_Type_Product_Attribute extends Mage_Core_Model_Abstract
{

    /**
     * Option ID to Value Mapping Array
     *
     * @var mixed
     */
    protected $_optionIdToValue;

    /**
     * @var array
     */
    protected $_exportAttributeCodes;

    /**
     * @var
     */
    protected $_engine;

    /**
     * @var array
     */
    protected $_exportAttributes;

    /**
     * @var array
     */
    protected $_configuredAttributes;

    /**
     * @var array
     */
    protected $_attributesByType;

    /**
     * @var array
     */
    protected $_searchableAttributes;


    protected function _construct()
    {
        parent::_construct();

        $this->_init('factfinder/attribute');
        $this->_engine = Mage::helper('catalogsearch')->getEngine();
    }


    /**
     * Retrieve attributes configured in FF backend
     *
     * @param int    $storeId
     * @param string $type
     *
     * @return mixed
     */
    public function getConfiguredAttributes($storeId, $type = null)
    {
        if (!isset($this->_configuredAttributes[$storeId])) {
            $configuredAttributes = Mage::getStoreConfig('factfinder/export/attributes', $storeId);
            $this->_configuredAttributes[$storeId] = unserialize($configuredAttributes);
        }

        if (!empty($type)) {
            $result = array();
            foreach ($this->_configuredAttributes[$storeId] as $code => $configuredAttribute) {
                if ($configuredAttribute['type'] == $type) {
                    $result[$code] = $configuredAttribute;
                }
            }

            return $result;
        }

        return $this->_configuredAttributes[$storeId];
    }


    /**
     * Get text of a select option
     *
     * @param $optionId
     * @param $storeId
     *
     * @return mixed|string
     */
    public function getOptionText($optionId, $storeId)
    {
        $value = '';
        if (intval($optionId)) {
            if ($this->_optionIdToValue === null) {
                /** @var Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection $optionCollection */
                $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection');
                $optionCollection->setStoreFilter($storeId);
                $this->_optionIdToValue = array();
                foreach ($optionCollection as $option) {
                    $this->_optionIdToValue[$option->getId()] = $option->getValue();
                }
            }

            $value = isset($this->_optionIdToValue[$optionId]) ? $this->_optionIdToValue[$optionId] : '';
        }

        return $value;
    }


    /**
     * Retrieve attribute source value for search
     * This method is mostly copied from Mage_CatalogSearch_Model_Resource_Fulltext,
     * but it also retrieves attribute values from non-searchable/non-filterable attributes
     *
     * @param int   $attributeId
     * @param mixed $value
     * @param int   $storeId
     *
     * @return mixed
     */
    public function getAttributeValue($attributeId, $value, $storeId)
    {
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
        $attribute = $this->getSearchableAttribute($attributeId);
        if (!$attribute->getIsSearchable() && $attribute->getAttributeCode() == 'visibility') {
            return $value;
        }

        if ($attribute->usesSource()) {
            if (method_exists($this->_engine, 'allowAdvancedIndex') && $this->_engine->allowAdvancedIndex()) {
                return $value;
            }

            $attribute->setStoreId($storeId);
            if(is_array($value)) {
                foreach ($value as $k => $val) {
                    $value[$k] = $attribute->getSource()->getOptionText($val);
                }
            }
            else {
                $value = $attribute->getSource()->getOptionText($value);
            }

            if (!is_array($value) && empty($value)) {
                $inputType = $attribute->getFrontend()->getInputType();
                if ($inputType == 'select' || $inputType == 'multiselect') {
                    return null;
                }
            }
        } elseif ($attribute->getBackendType() == 'datetime') {
            $value = strtotime($value) * 1000; // Java.lang.System.currentTimeMillis()
        } else {
            $inputType = $attribute->getFrontend()->getInputType();
            if ($inputType == 'price') {
                $value = Mage::app()->getStore($storeId)->roundPrice($value);
            }
        }

        return $value;
    }


    /**
     * Get attributes that can be used in search
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSearchableAttributes($storeId = null)
    {
        return $this->getResource()->getSearchableAttributes($storeId);
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
        if (!isset($this->_searchableAttributes[$attribute])) {
            $this->_searchableAttributes[$attribute] = $this->getResource()->getSearchableAttribute($attribute);
        }

        return $this->_searchableAttributes[$attribute];
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
        return $this->getResource()->getProductAttributes($storeId, $productIds, $attributeTypes);
    }


    /**
     * Get CSV Header Array
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getExportAttributes($storeId = 0)
    {
        if (!isset($this->_exportAttributeCodes[$storeId])) {
            $headerDynamic = array();

            // get dynamic Attributes
            foreach ($this->getSearchableAttributesByType(null, 'system', $storeId) as $attribute) {
                if (in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility'))) {
                    continue;
                }

                $headerDynamic[] = $attribute->getAttributeCode();
            }

            $configuredAttributes = array_keys($this->getConfiguredAttributes($storeId));
            if (Mage::helper('factfinder/export')->useExplicitAttributes($storeId)) {
                $headerDynamic = array_diff($headerDynamic, $configuredAttributes);
                $configuredAttributes = array();
            }


            $this->_exportAttributeCodes[$storeId] = array_values(array_unique(array_merge(
                $headerDynamic,
                $configuredAttributes
            )));
        }

        return $this->_exportAttributeCodes[$storeId];
    }


    /**
     * Get searchable attributes by type
     *
     * @param null   $backendType Backend type of the attributes
     * @param string $type        Possible Types: system, sortable, filterable, searchable
     * @param int    $storeId
     *
     * @return array
     */
    public function getSearchableAttributesByType($backendType = null, $type = null, $storeId = 0)
    {
        $cacheCode = $backendType . '|' . $type . '|' . $storeId;

        if (isset($this->_attributesByType[$cacheCode])) {
            return $this->_attributesByType[$cacheCode];
        }

        $attributes = array();

        if ($type !== null || $backendType !== null) {
            foreach ($this->getSearchableAttributes($storeId) as $attribute) {
                if ($backendType !== null && $attribute->getBackendType() != $backendType) {
                    continue;
                }

                if (!$this->_isAttributeOfType($attribute, $type, $storeId)) {
                    continue;
                }

                $attributes[$attribute->getId()] = $attribute;
            }
        } else {
            $attributes = $this->getSearchableAttributes($storeId);
        }

        $this->_attributesByType[$cacheCode] = $attributes;

        return $attributes;
    }


    /**
     * Check whether the attribute is of the requested type
     *
     * @param Mage_Catalog_Model_Resource_EAV_Attribute $attribute
     * @param string                                    $type
     * @param int                                       $storeId
     *
     * @return bool
     */
    protected function _isAttributeOfType($attribute, $type, $storeId = 0)
    {
        if ($type
            && isset($this->_attributesByType[$type][$storeId])
            && in_array($attribute->getAttributeCode(), $this->_attributesByType[$type][$storeId])
        ) {
            return true;
        }

        $isOfType = true;
        switch ($type) {
            case 'system':
                if ($attribute->getIsUserDefined() && !$attribute->getUsedForSortBy()) {
                    $isOfType = false;
                }
                break;
            case 'sortable':
                if (!$attribute->getUsedForSortBy()) {
                    $isOfType = false;
                }
                break;
            case 'filterable':
                $isOfType = $this->_isAttributeFilterable($attribute, $storeId);
                break;
            case 'numerical':
                $isOfType = $this->_isAttributeNumerical($attribute, $storeId);
                break;
            case 'searchable':
                $isOfType = $this->_isAttributeSearchable($attribute, $storeId);
                break;
            default:;
        }

        if ($type && $isOfType) {
            $this->_attributesByType[$type][$storeId][] = $attribute->getAttributeCode();
        }

        return $isOfType;
    }


    /**
     * Check if attribute is searchable
     *
     * @param Mage_Catalog_Model_Resource_EAV_Attribute $attribute
     * @param                                           $storeId
     *
     * @return bool
     */
    protected function _isAttributeSearchable($attribute, $storeId)
    {
        if ($this->_isAttributeNumerical($attribute, $storeId)) {
            return false;
        }

        if (!$attribute->getIsUserDefined()
            || $attribute->getIsSearchable()
            || in_array($attribute->getAttributeCode(), $this->getExportAttributes($storeId))
        ) {
            return true;
        }

        return false;
    }


    /**
     * Check if attribute is  filterable
     *
     * @param Mage_Catalog_Model_Resource_EAV_Attribute $attribute
     * @param int                                       $storeId
     *
     * @return bool
     */
    protected function _isAttributeFilterable($attribute, $storeId)
    {
        if ($this->_isAttributeNumerical($attribute, $storeId)) {
            return false;
        }

        if (Mage::helper('factfinder/export')->useExplicitAttributes($storeId)) {
            $attributes = $this->getConfiguredAttributes($storeId, 'text');
            if (in_array($attribute->getAttributeCode(), array_keys($attributes))) {
                return true;
            }
        }

        if (!$attribute->getIsFilterableInSearch()
            || in_array($attribute->getAttributeCode(), $this->getExportAttributes($storeId))
        ) {
            return false;
        }

        return true;
    }


    /**
     * Check if attribute is numerical
     *
     * @param Mage_Catalog_Model_Resource_EAV_Attribute $attribute
     * @param int                                       $storeId
     *
     * @return bool
     */
    protected function _isAttributeNumerical($attribute, $storeId)
    {
        if (Mage::helper('factfinder/export')->useExplicitAttributes($storeId)) {
            $attributes = $this->getConfiguredAttributes($storeId, 'number');
            if (in_array($attribute->getAttributeCode(), array_keys($attributes))) {
                return true;
            }
        }

        if (!$attribute->getIsFilterableInSearch()
            || in_array($attribute->getAttributeCode(), $this->getExportAttributes($storeId))
            || $attribute->getBackendType() != 'decimal'
        ) {
            return false;
        }

        return true;
    }


    /**
     * Get array of static product fields
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getStaticFields($storeId)
    {
        $staticFields = array();
        foreach ($this->getSearchableAttributesByType('static', 'system', $storeId) as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        return $staticFields;
    }


    /**
     * Get array of dynamic fields to use in csv
     *
     * @param $storeId
     *
     * @return array
     */
    public function getDynamicFields($storeId)
    {
        $dynamicFields = array();
        foreach (array('int', 'varchar', 'text', 'decimal', 'datetime') as $type) {
            $dynamicFields[$type] = array_keys($this->getSearchableAttributesByType($type, null, $storeId));
        }

        return $dynamicFields;
    }


    /**
     * Get Attribute Row Array
     *
     * @param array $dataArray Export row Array
     * @param array $values    Attributes Array
     * @param int   $storeId   Store ID
     * @param array $productData
     *
     * @return array
     */
    public function addAttributesToRow($dataArray, $values, $storeId = 0, $productData = array())
    {
        // get attributes objects assigned to their position at the export
        if (empty($this->_exportAttributes[$storeId])) {
            $attributes = $this->getExportAttributes($storeId);
            $this->_exportAttributes[$storeId] = array_fill_keys(
                $attributes,
                null
            );

            $searchableAttributes = $this->getSearchableAttributesByType(null, null, $storeId);
            foreach ($searchableAttributes as $attribute) {
                if (in_array($attribute->getAttributeCode(), $attributes)
                    && !in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility'))
                ) {
                    $this->_exportAttributes[$storeId][$attribute->getAttributeCode()] = $attribute;
                }
            }
        }

        // fill dataArray with the values of the attributes that should be exported
        foreach ($this->_exportAttributes[$storeId] as $code => $attribute) {
            if ($attribute != null) {
                $value = isset($values[$attribute->getId()]) ? $values[$attribute->getId()] : null;
                if (empty($value)) {
                    $value = isset($productData[$code]) ? $productData[$code] : null;
                }
                $value = $this->getAttributeValue($attribute->getId(), $value, $storeId);
                $value = $this->_removeTags($value, $storeId);
                $dataArray[$code] = $value;
            } else {
                $dataArray[$code] = null;
            }
        }

        return $dataArray;
    }


    /**
     * Format attributes for csv
     *
     * @param string   $type    Possible values: filterable|searchable|numerical
     * @param array    $values
     * @param null|int $storeId
     *
     * @return string
     */
    public function formatAttributes($type, $values, $storeId = null)
    {
        $attributes = $this->getSearchableAttributesByType(null, $type, $storeId);

        $returnArray = array();
        $counter = 0;

        foreach ($attributes as $attribute) {
            $attributeValue = isset($values[$attribute->getId()]) ? $values[$attribute->getId()] : null;
            if (!$attributeValue
                || in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility', 'price'))
            ) {
                continue;
            }

            $attributeValues = $this->getAttributeValue($attribute->getId(), $attributeValue, $storeId);

            if (!is_array($attributeValues)) {
                $attributeValues = array($attributeValues);
            }

            $attributeValues = $this->_filterAttributeValues($attributeValues);
            foreach ($attributeValues as $attributeValue) {
                $attributeValue = $this->_removeTags($attributeValue, $storeId);
                if ($type == 'searchable') {
                    $returnArray[] = $attributeValue;
                } elseif ($type == 'numerical') {
                    $returnArray[] = $this->_formatNumericalAttributes($storeId, $attribute, $attributeValue);
                } else {
                    $attributeCode = $this->_removeTags($attribute->getAttributeCode(), $storeId);
                    $attributeValue = str_replace(array('|', '=', '#'), '', array($attributeCode, $attributeValue));
                    $returnArray[] = implode('=', $attributeValue);
                }
            }

            // apply field limit as required by ff
            $counter++;
            if ($counter >= 1000) {
                break;
            }
        }

        $delimiter = ($type == 'searchable' ? ',' : '|');

        return implode($delimiter, $returnArray);
    }


    /**
     * Check if html tags and entities should be removed on export
     *
     * @param string $value
     * @param int    $storeId
     *
     * @return bool
     */
    protected function _removeTags($value, $storeId)
    {
        if (Mage::getStoreConfig('factfinder/export/remove_tags', $storeId)) {
            $attributeValues = $value;
            if (!is_array($attributeValues)) {
                $attributeValues = array($value);
            }
            foreach ($attributeValues as &$attributeValue) {
                // decode html entities
                $attributeValue = html_entity_decode($attributeValue, null, 'UTF-8');
                // Add spaces before HTML Tags, so that strip_tags() does not join word which were in different block elements
                // Additional spaces are not an issue, because they will be removed in the next step anyway
                $attributeValue = preg_replace('/</u', ' <', $attributeValue);
                $attributeValue = preg_replace("#\s+#siu", ' ', trim(strip_tags($attributeValue)));
                // remove rest html entities
                $attributeValue = preg_replace("/&(?:[a-z\d]|#\d|#x[a-f\d]){2,8};/i", '', $attributeValue);
            }
            $value = implode("|", $attributeValues);
        }

        return $value;
    }


    /**
     * Remove all empty values from array
     *
     * @param array $values
     *
     * @return array
     */
    protected function _filterAttributeValues($values)
    {
        // filter all empty values out
        return array_filter($values, function ($value) {
            return !empty($value);
        });
    }


    /**
     * Format numerical attribute value
     *
     * @param int                                       $storeId
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string                                    $attributeValue
     *
     * @return array
     */
    protected function _formatNumericalAttributes($storeId, $attribute, $attributeValue)
    {
        $attributeCode = $attribute->getAttributeCode();
        if (Mage::helper('factfinder/export')->useExplicitAttributes($storeId)) {
            // make sure attributes are loaded
            $this->getConfiguredAttributes($storeId);

            // add unit if available
            if (isset($this->_configuredAttributes[$storeId][$attributeCode])) {
                $configuredAttribute = $this->_configuredAttributes[$storeId][$attributeCode];
                if (!empty($configuredAttribute['unit'])) {
                    $unit = $configuredAttribute['unit'];
                    $attributeCode .= '~~' . $unit;
                }
            }
        }

        $attributeCode = $this->_removeTags($attributeCode, $storeId);

        // bring it to the en_US format without thousand separator (eg. 2134.7639)
        $attributeValue = round(number_format(floatval($attributeValue), 4, '.', ''), 4);
        // remove forbidden symbols
        $attributeValue = str_replace(array('|', '=', '#'), '', array($attributeCode, $attributeValue));

        return implode('=', $attributeValue);
    }


}