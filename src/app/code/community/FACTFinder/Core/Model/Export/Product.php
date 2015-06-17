<?php
//TODO this class needs a sufficient refactoring !!!
/**
 * Model class
 *
 * This helper class provides the Product export
 *
 */
class FACTFinder_Core_Model_Export_Product extends Mage_CatalogSearch_Model_Resource_Fulltext
{

    /**
     * Option ID to Value Mapping Array
     * @var mixed
     */
    protected $_optionIdToValue = null;

    /**
     * Products to Category Path Mapping
     *
     * @var mixed
     */
    protected $_productsToCategoryPath = null;

    /**
     * Category Names by ID
     * @var mixed
     */
    protected $_categoryNames = null;

    /**
     * export attribute codes
     * @var mixed
     */
    protected $_exportAttributeCodes = null;

    /**
     * export attribute objects
     * @var mixed
     */
    protected $_exportAttributes = null;

    /**
     * helper to generate the image urls
     * @var Mage_Catalog_Helper_Image
     */
    protected $_imageHelper = null;

    /**
     * add CSV Row
     *
     * @param array $data
     */
    protected $_lines = array();

    /**
     * @var FACTFinder_Core_Model_File
     */
    protected $_file = null;


    /**
     * @var array
     */
    protected $_deafultHeader = array(
        'id',
        'parent_id',
        'sku',
        'category',
        'filterable_attributes',
        'searchable_attributes',
    );


    /**
     * Add row to csv
     *
     * @param array $data Array of data
     */
    protected function _addCsvRow($data)
    {
        foreach ($data as &$item) {
            $item = str_replace(array("\r", "\n"), array(' ', ' '), trim(strip_tags($item), ';'));
            $item = addslashes($item);
        }

        $this->_lines[] = '"' . implode('";"', $data) . '"' . "\n";
    }


    /**
     * Add row to csv
     *
     * @param array $data Array of data
     * @param int   $storeId
     */
    protected function _writeCsvRow($data, $storeId)
    {
        foreach ($data as &$item) {
            $item = str_replace(array("\r", "\n"), array(' ', ' '), trim(strip_tags($item), ';'));
            $item = addslashes($item);
        }

        $line = '"' . implode('";"', $data) . '"' . "\n";
        $this->_getFile($storeId)->write($line);
    }


    /**
     * get Option Text by Option ID
     *
     * @param int $optionId Option ID
     * @param int $storeId  Store ID
     *
     * @return string
     */
    protected function _getAttributeOptionText($optionId, $storeId)
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
     * Get CSV Header Array
     *
     * @param int $storeId
     *
     * @return array
     */
    protected function _getExportAttributes($storeId = 0)
    {
        if (!isset($this->_exportAttributeCodes[$storeId])) {
            $headerDynamic = array();

            if (Mage::getStoreConfigFlag('factfinder/export/urls', $storeId)) {
                $this->_deafultHeader[] = 'image';
                $this->_deafultHeader[] = 'deeplink';
                $this->_imageHelper = Mage::helper('catalog/image');
            }

            // get dynamic Attributes
            foreach ($this->_getSearchableAttributes(null, 'system', $storeId) as $attribute) {
                if (in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility'))) {
                    continue;
                }

                $headerDynamic[] = $attribute->getAttributeCode();
            }

            // compare dynamic with setup attributes
            $headerSetup = Mage::helper('factfinder/backend')
                ->unserializeFieldValue(Mage::getStoreConfig('factfinder/export/attributes', $storeId));
            foreach ($headerDynamic as $code) {
                if (in_array($code, $headerSetup)) {
                    continue;
                }

                $headerSetup[$code]['attribute'] = $code;
            }

            // remove default attributes from setup
            foreach ($this->_deafultHeader as $code) {
                if (array_key_exists($code, $headerSetup)) {
                    unset($headerSetup[$code]);
                }
            }

            $this->_exportAttributeCodes[$storeId] = array_merge($this->_deafultHeader, array_keys($headerSetup));
        }

        return $this->_exportAttributeCodes[$storeId];
    }


    /**
     * Pre-Generate all product exports for all stores
     *
     * @return array
     */
    public function saveAll()
    {
        $paths = array();
        $stores = Mage::app()->getStores();
        foreach ($stores as $id => $store) {
            try {
                $paths[] = $this->saveExport($id);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $paths;
    }


    /**
     * Generate product export for specific store
     *
     * @param int $storeId
     *
     * @return string
     */
    public function saveExport($storeId = 0)
    {
        $dir = Mage::getBaseDir('var') . DS . 'factfinder';

        try {
            $fileName = 'store_' . $storeId . '_product.csv';

            $this->doExport($storeId);

        } catch (Exception $e) {
            Mage::throwException($e);
            return '';
        }

        return $dir . DS . $fileName;
    }


    /**
     * Get file handler instance for store
     *
     * @param $storeId
     *
     * @return \FACTFinder_Core_Model_File
     *
     * @throws \Exception
     */
    protected function _getFile($storeId)
    {
        if (!$this->_file) {
            $dir = Mage::getBaseDir('var') . DS . 'factfinder';

            $fileName = 'store_' . $storeId . '_product.csv';

            $this->_file = Mage::getModel('factfinder/file');

            $this->_file->open($dir, $fileName);
        }

        return $this->_file;
    }


    /**
     * Export Product Data with Attributes
     * direct Output as CSV
     *
     * @param int $storeId Store View Id
     *
     * @return array
     */
    public function doExport($storeId = null)
    {
        // reset lines
        $this->_lines = array();

        $baseAdminUrl = Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        if ($storeId !== null) {
            $currentBaseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        }

        $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();
        $exportImageAndDeeplink = Mage::getStoreConfigFlag('factfinder/export/urls', $storeId);
        if ($exportImageAndDeeplink) {
            $imageType = Mage::getStoreConfig('factfinder/export/suggest_image_type', $storeId);
            $imageSize = (int)Mage::getStoreConfig('factfinder/export/suggest_image_size', $storeId);
        }

        $header = $this->_getExportAttributes($storeId);
        $this->_writeCsvRow($header, $storeId);

        // preparesearchable attributes
        $staticFields = array();
        foreach ($this->_getSearchableAttributes('static', 'system', $storeId) as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }

        $dynamicFields = array();
        foreach (array('int', 'varchar', 'text', 'decimal', 'datetime') as $type) {
            $dynamicFields[$type] = array_keys($this->_getSearchableAttributes($type));
        }

        // status and visibility filter
        $visibility = $this->_getSearchableAttribute('visibility');
        $status = $this->_getSearchableAttribute('status');
        $visibilityVals = Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
        $statusVals = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();

        $lastProductId = 0;
        while (true) {
            $products = $this->_getSearchableProducts($storeId, $staticFields, null, $lastProductId);
            if (!$products) {
                break;
            }

            $productRelations = array();
            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChilds = $this->_getProductChildIds($productData['entity_id'], $productData['type_id']);
                $productRelations[$productData['entity_id']] = $productChilds;
                if ($productChilds) {
                    foreach ($productChilds as $productChild) {
                        $productAttributes[$productChild['entity_id']] = $productChild;
                    }
                }
            }

            $productAttributes = $this->_getProductAttributes($storeId, array_keys($productAttributes), $dynamicFields);
            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];

                if (!isset($productAttr[$visibility->getId()]) || !in_array($productAttr[$visibility->getId()], $visibilityVals)) {
                    continue;
                }

                if (!isset($productAttr[$status->getId()]) || !in_array($productAttr[$status->getId()], $statusVals)) {
                    continue;
                }

                $productIndex = array(
                    $productData['entity_id'],
                    $productData[$idFieldName],
                    $productData['sku'],
                    $this->_getCategoryPath($productData['entity_id'], $storeId),
                    $this->_formatAttributes('filterable', $productAttr, $storeId),
                    $this->_formatAttributes('searchable', $productAttr, $storeId),
                );

                if ($exportImageAndDeeplink) {
                    $product = Mage::getModel("catalog/product");
                    $product->setStoreId($storeId);
                    $product->load($productData['entity_id']);


                    $image = $this->_imageHelper->init($product, $imageType);
                    if (isset($imageSize) && $imageSize > 0) {
                        $image->resize($imageSize);
                    }
                    $image = (string)$image;

                    if (!is_null($storeId)) {
                        $image = str_replace($baseAdminUrl, $currentBaseUrl, $image);
                    }
                    $productIndex[] = $image;
                    $productIndex[] = $product->getProductUrl();
                    $product->clearInstance();
                }

                $this->_getAttributesRowArray($productIndex, $productAttr, $storeId);

                $this->_writeCsvRow($productIndex, $storeId);

                $productChilds = $productRelations[$productData['entity_id']];
                if ($productChilds) {
                    foreach ($productChilds as $productChild) {
                        if (isset($productAttributes[$productChild['entity_id']])) {
                            /* should be used if sub products should not be exported because of their status
                            $subProductAttr = $productAttributes[$productChild[ 'entity_id' ]];
                            if (!isset($subProductAttr[$status->getId()]) || !in_array($subProductAttr[$status->getId()], $statusVals)) {
                                continue;
                            } */

                            $subProductIndex = array(
                                $productChild['entity_id'],
                                $productData[$idFieldName],
                                $productChild['sku'],
                                $this->_getCategoryPath($productData['entity_id'], $storeId),
                                $this->_formatAttributes('filterable', $productAttr, $storeId),
                                $this->_formatAttributes('searchable', $productAttr, $storeId),
                            );
                            if ($exportImageAndDeeplink) {
                                //dont need to add image and deeplink to child product, just add empty values
                                $subProductIndex[] = '';
                                $subProductIndex[] = '';
                            }
                            $this->_getAttributesRowArray($subProductIndex, $productAttributes[$productChild['entity_id']], $storeId);

                            $this->_writeCsvRow($subProductIndex, $storeId);
                        }
                    }
                }
            }
        }

        return $this->_lines;
    }


    /**
     * Format attributes for csv
     *
     * @param string   $type
     * @param array    $values
     * @param null|int $storeId
     *
     * @return string
     */
    protected function _formatAttributes($type, $values, $storeId = null)
    {
        $attributes = $this->_getSearchableAttributes(null, $type);

        $returnArray = array();
        $counter = 0;

        foreach ($attributes as $attribute) {
            $value = isset($values[$attribute->getId()]) ? $values[$attribute->getId()] : null;
            if (!$value || in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility', 'price'))) {
                continue;
            }

            $attributeValue = $this->_getAttributeValue($attribute->getId(), $value, $storeId);

            $attributeValues = explode('|', $attributeValue);
            $attributeValues = $this->_filterAttributeValues($attributeValues);
            foreach ($attributeValues as $value) {
                if ($type == 'filterable') {
                    $returnArray[] = $attribute->getAttributeCode() . '=' . $value;
                } else {
                    $returnArray[] = $attributeValue;
                }
            }

            // apply field limit as required by ff
            $counter++;
            if ($counter >= 128) {
                break;
            }
        }

        $delimiter = $type == 'filterable' ? '|' : ',';

        return implode($delimiter, $returnArray);
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
     * Retrieve Searchable attributes
     *
     * @param string $backendType
     * @param string $type possible Types: system, sortable, filterable, searchable
     * @return array
     */
    protected function _getSearchableAttributes($backendType = null, $type = null, $storeId = null)
    {
        if (is_null($this->_searchableAttributes)) {
            $this->_searchableAttributes = array();
            $entityType = $this->getEavConfig()->getEntityType('catalog_product');
            $entity = $entityType->getEntity();

            $userDefinedAttributes = array_keys(Mage::helper('factfinder/backend')->unserializeFieldValue(Mage::getStoreConfig('factfinder/export/attributes', $storeId)));

            $whereCond = array(
                $this->_getWriteAdapter()->quoteInto('additional_table.is_searchable=? or additional_table.is_filterable=? or additional_table.used_for_sort_by=?', 1),
                $this->_getWriteAdapter()->quoteInto('main_table.attribute_code IN(?)', array_merge(array('status', 'visibility'), $userDefinedAttributes))
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

        if (!is_null($type) || !is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchableAttributes as $attribute) {
                if (!is_null($backendType)
                    && $attribute->getBackendType() != $backendType
                ) {
                    continue;
                }

                if ($this->_checkIfSkipAttribute($attribute, $type)) {
                    continue;
                }

                $attributes[$attribute->getId()] = $attribute;
            }

            return $attributes;
        }

        return $this->_searchableAttributes;
    }


    /**
     * Get Category Path by Product ID
     *
     * @param   int $productId
     * @param    int $storeId
     *
     * @return  string
     */
    protected function _getCategoryPath($productId, $storeId = null)
    {

        if ($this->_categoryNames === null) {
            $categoryCollection = Mage::getResourceModel('catalog/category_attribute_collection');
            $categoryCollection->getSelect()->where("attribute_code IN('name', 'is_active')");

            foreach ($categoryCollection as $categoryModel) {
                ${$categoryModel->getAttributeCode() . 'Model'} = $categoryModel;
            }

            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('main' => $nameModel->getBackendTable()),
                    array('entity_id', 'value')
                )
                ->join(
                    array('e' => $is_activeModel->getBackendTable()),
                    'main.entity_id=e.entity_id AND (e.store_id = 0 OR e.store_id = ' . $storeId . ') AND e.attribute_id=' . $is_activeModel->getAttributeId(),
                    null
                )
                ->where('main.attribute_id=?', $nameModel->getAttributeId())
                ->where('e.value=?', '1')
                ->where('main.store_id = 0 OR main.store_id = ?', $storeId);

            $this->_categoryNames = $this->_getReadAdapter()->fetchPairs($select);
        }

        if ($this->_productsToCategoryPath === null) {
            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('main' => $this->getTable('catalog/category_product_index')),
                    array('product_id')
                )
                ->join(
                    array('e' => $this->getTable('catalog/category')),
                    'main.category_id=e.entity_id',
                    null
                )
                ->columns(array('e.path' => new Zend_Db_Expr('GROUP_CONCAT(e.path)')))
                ->where(
                    'main.visibility IN(?)',
                    array(
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                    )
                )
                ->where('main.store_id = ?', $storeId)
                ->where('e.path LIKE \'1/' . Mage::app()->getStore($storeId)->getRootCategoryId() . '/%\'')
                ->group('main.product_id');

            $this->_productsToCategoryPath = $this->_getReadAdapter()->fetchPairs($select);
        }

        $value = '';
        if (isset($this->_productsToCategoryPath[$productId])) {
            $paths = explode(',', $this->_productsToCategoryPath[$productId]);
            foreach ($paths as $path) {
                $categoryIds = explode('/', $path);
                $categoryIdsCount = count($categoryIds);
                $categoryPath = '';
                for ($i = 2; $i < $categoryIdsCount; $i++) {
                    if (!isset($this->_categoryNames[$categoryIds[$i]])) {
                        continue 2;
                    }
                    $categoryPath .= urlencode(trim($this->_categoryNames[$categoryIds[$i]])) . '/';
                }
                if ($categoryIdsCount > 2) {
                    $value .= rtrim($categoryPath, '/') . '|';
                }
            }
            $value = trim($value, '|');
        }

        return $value;
    }


    /**
     * Return all product children ids
     *
     * @param int $productId Product Entity Id
     * @param string $typeId Super Product Link Type
     * @return array
     */
    protected function _getProductChildIds($productId, $typeId)
    {
        $typeInstance = $this->_getProductTypeInstance($typeId);
        $relation = $typeInstance->isComposite()
            ? $typeInstance->getRelationInfo()
            : false;

        if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('main' => $this->getTable($relation->getTable())),
                    array($relation->getChildFieldName()))
                ->join(
                    array('e' => $this->getTable('catalog/product')),
                    'main.' . $relation->getChildFieldName() . '=e.entity_id',
                    array('entity_id', 'type_id', 'sku')
                )
                ->where("{$relation->getParentFieldName()}=?", $productId);
            if (!is_null($relation->getWhere())) {
                $select->where($relation->getWhere());
            }
            return $this->_getReadAdapter()->fetchAll($select);
        }

        return null;
    }

    /**
     * Retrieve attribute source value for search
     * This method is mostly copied from Mage_CatalogSearch_Model_Resource_Fulltext, but it also retrieves attribute values from non-searchable/non-filterable attributes
     *
     * @param int $attributeId
     * @param mixed $value
     * @param int $storeId
     * @return mixed
     */
    protected function _getAttributeValue($attributeId, $value, $storeId)
    {
        $attribute = $this->_getSearchableAttribute($attributeId);
        if (!$attribute->getIsSearchable() && $attribute->getAttributeCode() == 'visibility') {
            return $value;
        }

        if ($attribute->usesSource()) {
            if (method_exists($this->_engine, 'allowAdvancedIndex') && $this->_engine->allowAdvancedIndex()) {
                return $value;
            }

            $attribute->setStoreId($storeId);
            $value = $attribute->getSource()->getOptionText($value);

            if (is_array($value)) {
                $value = implode($this->_separator, $value);
            } elseif (empty($value)) {
                $inputType = $attribute->getFrontend()->getInputType();
                if ($inputType == 'select' || $inputType == 'multiselect') {
                    return null;
                }
            }
        } elseif ($attribute->getBackendType() == 'datetime') {
            $value = $this->_getStoreDate($storeId, $value);
        } else {
            $inputType = $attribute->getFrontend()->getInputType();
            if ($inputType == 'price') {
                $value = Mage::app()->getStore($storeId)->roundPrice($value);
            }
        }

        // Add spaces before HTML Tags, so that strip_tags() does not join word which were in different block elements
        // Additional spaces are not an issue, because they will be removed in the next step anyway
        $value = preg_replace('/</u', ' <', $value);

        $value = preg_replace("#\s+#siu", ' ', trim(strip_tags($value)));

        return $value;
    }

    /**
     * get Attribute Row Array
     *
     * @param array $dataArray Export row Array
     * @param array $attributes Attributes Array
     * @param int $storeId Store ID
     */
    protected function _getAttributesRowArray(&$dataArray, $values, $storeId = null)
    {
        // get attributes objects assigned to their position at the export
        if ($this->_exportAttributes == null) {
            $this->_exportAttributes = array_fill(0, sizeof($this->_getExportAttributes($storeId)), null);

            $attributeCodes = array_flip($this->_getExportAttributes($storeId));
            foreach ($this->_getSearchableAttributes() as $attribute) {
                if (isset($attributeCodes[$attribute->getAttributeCode()]) && !in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility'))) {
                    $this->_exportAttributes[$attributeCodes[$attribute->getAttributeCode()]] = $attribute;
                }
            }
        }
        // fill dataArray with the values of the attributes that should be exported
        foreach ($this->_exportAttributes AS $pos => $attribute) {
            if ($attribute != null) {
                $value = isset($values[$attribute->getId()]) ? $values[$attribute->getId()] : null;
                $dataArray[$pos] = $this->_getAttributeValue($attribute->getId(), $value, $storeId);
            } else if (!array_key_exists($pos, $dataArray)) {
                // it is very unlikely that an attribute exists in the header but is not delivered by "getSearchableAttributes",
                // but actually it might be a result of a broken database or something like that..
                $dataArray[$pos] = null;
            }
        }
    }


    /**
     * Check whether the attribute should be skipped
     *
     * @param $attribute
     * @param $type
     *
     * @return bool
     */
    protected function _checkIfSkipAttribute($attribute, $type)
    {
        $shouldSkip = false;
        switch ($type) {
            case "system":
                if ($attribute->getIsUserDefined() && !$attribute->getUsedForSortBy()) {
                    $shouldSkip = true;
                }
                break;
            case "sortable":
                if (!$attribute->getUsedForSortBy()) {
                    $shouldSkip = true;
                }
                break;
            case "filterable":
                if (!$attribute->getIsFilterableInSearch()
                    || in_array($attribute->getAttributeCode(), $this->_getExportAttributes())
                ) {
                    $shouldSkip = true;
                }
                break;
            case "searchable":
                if (!$attribute->getIsUserDefined()
                    || !$attribute->getIsSearchable()
                    || in_array($attribute->getAttributeCode(), $this->_getExportAttributes())
                ) {
                    $shouldSkip = true;
                }
                break;
            default:
                ;
        }

        return $shouldSkip;
    }

}
