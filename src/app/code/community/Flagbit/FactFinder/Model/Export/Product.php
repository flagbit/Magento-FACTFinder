<?php 
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Model class
 * 
 * This helper class provides the Product export
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Model_Export_Product extends Mage_CatalogSearch_Model_Mysql4_Fulltext {

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
    protected function _addCsvRow($data)
    {           
        foreach ($data as &$item) {
            $item = str_replace(array("\r", "\n", "\""), array(' ', ' ', "''"), trim( strip_tags($item), ';') );
        }

        echo '"'.implode('";"', $data).'"'."\n";
    }    
    
    /**
     * get Option Text by Option ID
     * 
     * @param int $optionId Option ID
     * @param int $storeId Store ID
     * @return string 
     */
    protected function _getAttributeOptionText($optionId, $storeId)
    {      
        $value = '';
        if (intval($optionId)) {      
            if ($this->_optionIdToValue === null) {
                /*@var $optionCollection Mage_Eav_Model_Mysql4_Entity_Attribute_Option_Collection */        
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
     * get CSV Header Array
     * 
     * @param int $storeId
     * @return array
     */
    protected function _getExportAttributes($storeId = null) 
    {
        if($this->_exportAttributeCodes === null){
            $headerDefault = array('id', 'parent_id', 'sku', 'category', 'filterable_attributes', 'searchable_attributes');
            $headerDynamic = array();
            
            if (Mage::getStoreConfigFlag('factfinder/export/urls', $storeId)) {
                $headerDefault[] = 'image';
                $headerDefault[] = 'deeplink';
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
            $headerSetup = Mage::helper('factfinder/backend')->makeArrayFieldValue(Mage::getStoreConfig('factfinder/export/attributes', $storeId));
            $setupUpdate = false;
            foreach($headerDynamic as $code){
                if(in_array($code, $headerSetup)){
                    continue;
                }
                $headerSetup[$code]['attribute'] = $code;
                $setupUpdate = true;
            }
            if($setupUpdate === true){
                Mage::getModel('core/config')->saveConfig('factfinder/export/attributes', Mage::helper('factfinder/backend')->makeStorableArrayFieldValue($headerSetup), 'stores', $storeId);
            }         
            
            $this->_exportAttributeCodes = array_merge($headerDefault, array_keys($headerSetup));
        }
        return $this->_exportAttributeCodes;
    }
    
    
    /**
     * export Product Data with Attributes
     * direct Output as CSV
     *
     * @param int $storeId Store View Id
     */
    public function doExport($storeId = null)
    {
        $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();
        $exportImageAndDeeplink = Mage::getStoreConfigFlag('factfinder/export/urls', $storeId);

        $header = $this->_getExportAttributes($storeId);
        $this->_addCsvRow($header);
        
        // preparesearchable attributes
        $staticFields   = array();
        foreach ($this->_getSearchableAttributes('static', 'system', $storeId) as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        $dynamicFields  = array(
            'int'       => array_keys($this->_getSearchableAttributes('int')),
            'varchar'   => array_keys($this->_getSearchableAttributes('varchar')),
            'text'      => array_keys($this->_getSearchableAttributes('text')),
            'decimal'   => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime'  => array_keys($this->_getSearchableAttributes('datetime')),
        );

        // status and visibility filter
        $visibility     = $this->_getSearchableAttribute('visibility');
        $status         = $this->_getSearchableAttribute('status');
        $visibilityVals = Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
        $statusVals     = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();

        $lastProductId = 0;
        while (true) {
            $products = $this->_getSearchableProducts($storeId, $staticFields, null, $lastProductId);
            if (!$products) {
                break;
            }

            $productRelations   = array();
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

            $productIndexes     = array();
            $productAttributes  = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);
            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }
                $protductAttr = $productAttributes[$productData['entity_id']];
                
                if (!isset($protductAttr[$visibility->getId()]) || !in_array($protductAttr[$visibility->getId()], $visibilityVals)) {
                    continue;
                }
                if (!isset($protductAttr[$status->getId()]) || !in_array($protductAttr[$status->getId()], $statusVals)) {
                    continue;
                }
                
                $productIndex = array(
                        $productData['entity_id'], 
                        $productData[$idFieldName], 
                        $productData['sku'], 
                        $this->_getCategoryPath($productData['entity_id'], $storeId),
                        $this->_formatFilterableAttributes($this->_getSearchableAttributes(null, 'filterable'), $protductAttr, $storeId),
                        $this->_formatSearchableAttributes($this->_getSearchableAttributes(null, 'searchable'), $protductAttr, $storeId)
                    );

                if ($exportImageAndDeeplink) {
                    $product = Mage::getModel("catalog/product");
                    $product->load($productData['entity_id']);
                    
                    $productIndex[] = $this->_imageHelper->init($product, 'image')->resize(120)->__toString();
                    $productIndex[] = $product->getProductUrl();
                }
                
                $this->_getAttributesRowArray($productIndex, $protductAttr, $storeId);
                               
                $this->_addCsvRow($productIndex);
                
                if ($productChilds = $productRelations[$productData['entity_id']]) {       
                    foreach ($productChilds as $productChild) {
                        if (isset($productAttributes[$productChild['entity_id']])) {

                            $subProductIndex = array(
                                    $productChild['entity_id'],
                                    $productData[$idFieldName],
                                    $productChild['sku'],
                                    $this->_getCategoryPath($productData['entity_id'], $storeId),
                                    $this->_formatFilterableAttributes($this->_getSearchableAttributes(null, 'filterable'), $productAttributes[$productChild['entity_id']], $storeId),
                                    $this->_formatSearchableAttributes($this->_getSearchableAttributes(null, 'searchable'), $productAttributes[$productChild['entity_id']], $storeId)                                    
                                );
                            if ($exportImageAndDeeplink) {
                                //dont need to add image and deeplink to child product, just add empty values
                                $subProductIndex[] = '';
                                $subProductIndex[] = '';
                            }
                            $this->_getAttributesRowArray($subProductIndex, $productAttributes[$productChild['entity_id']], $storeId);

                            $this->_addCsvRow($subProductIndex);
                        }
                    }
                }             
            }
            flush();
        }
    }
    
    protected function _formatSearchableAttributes($attributes, $values, $storeId=null)
    {
        $returnArray = array();
        foreach ($attributes as $attribute) {
            $value = isset($values[$attribute->getId()]) ? $values[$attribute->getId()] : null;
            if (!$value || in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility', 'price'))) {
                continue;
            }
            $attributeValue = $this->_getAttributeValue($attribute->getId(), $value, $storeId);
            if (strval($attributeValue) != "") {
                $returnArray[] = $attributeValue;
            }
        }        
        return implode(',', $returnArray);  
    }

    protected function _formatFilterableAttributes($attributes, $values, $storeId=null)
    {
        $returnArray = array();
        foreach ($attributes as $attribute) {
            $value = isset($values[$attribute->getId()]) ? $values[$attribute->getId()] : null;
            if (!$value || in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility', 'price'))) {
                continue;
            }            
            $attributeValue = $this->_getAttributeValue($attribute->getId(), $value, $storeId);
            $attributeValues = array();
            if (strpos($attributeValue, '|') !== false) {
                $attributeValues = explode('|', $attributeValue);
            } else {
                $attributeValues[] = $attributeValue;
            }
            
            foreach ($attributeValues AS $value) {
                if (strval($value) != "") {
                    $returnArray[] = $attribute->getAttributeCode().'='.$value;
                }
            }
        }        
        return implode('|', $returnArray);              
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
            $entity     = $entityType->getEntity();

			$userDefinedAttributes = array_keys(Mage::helper('factfinder/backend')->makeArrayFieldValue(Mage::getStoreConfig('factfinder/export/attributes', $storeId)));
			
            $whereCond  = array(
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
            unset($attributesData);
        }

        if (!is_null($type) || !is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchableAttributes as $attribute) {
                
                if (!is_null($backendType) 
                    && $attribute->getBackendType() != $backendType) {
                        continue;
                    }
                
                switch($type) {
                    
                    case "system":
                        if ($attribute->getIsUserDefined() 
                            && !$attribute->getUsedForSortBy()) {
                            continue 2;
                        }
                        break;

                    case "sortable":
                        if (!$attribute->getUsedForSortBy()) {
                            continue 2;
                        }
                        break;

                    case "filterable":
                        if (!$attribute->getIsFilterableInSearch()
                            || in_array($attribute->getAttributeCode(), $this->_getExportAttributes())) {
                            continue 2;
                        }
                        break;

                    case "searchable":
                        if (!$attribute->getIsUserDefined()
                            || !$attribute->getIsSearchable()
                            || in_array($attribute->getAttributeCode(), $this->_getExportAttributes())) {
                            continue 2;
                        }
                        break;
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
     * @return  string
     */
    protected function _getCategoryPath($productId, $storeId = null)
    {

        if ($this->_categoryNames === null) {
            $categoryCollection = Mage::getResourceModel('catalog/category_attribute_collection');
            $categoryCollection->getSelect()->where("attribute_code IN('name', 'is_active')");
            
            foreach ($categoryCollection as $categoryModel) {
                ${$categoryModel->getAttributeCode().'Model'} = $categoryModel;
            }

            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('main' => $nameModel->getBackendTable()),
                    array('entity_id', 'value')
                    )
                ->join(
                    array('e' => $is_activeModel->getBackendTable()),
                    'main.entity_id=e.entity_id AND (e.store_id = 0 OR e.store_id = '.$storeId.') AND e.attribute_id='.$is_activeModel->getAttributeId(),
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
                ->where('main.visibility IN(3,4)') //TODO look for Constants
                ->where('main.store_id = ?', $storeId)
                ->where('e.path LIKE \'1/' . Mage::app()->getStore()->getRootCategoryId() .'/%\'')
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
                for($i=2;$i < $categoryIdsCount;$i++) {
                    if (!isset($this->_categoryNames[$categoryIds[$i]])) {
                        continue 2;
                    }
                    $categoryPath .= urlencode($this->_categoryNames[$categoryIds[$i]]).'/';
                }
                if ($categoryIdsCount > 2) {
                    $value .= rtrim($categoryPath,'/').'|';
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
                    'main.'.$relation->getChildFieldName().'=e.entity_id',
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
     * get Attribute Row Array
     * 
     * @param array $dataArray Export row Array
     * @param array $attributes Attributes Array
     * @param int $storeId Store ID
     */
    protected function _getAttributesRowArray(&$dataArray, $values, $storeId=null)
    {
		// get attributes objects assigned to their position at the export
		if ($this->_exportAttributes == null) {
			$this->_exportAttributes = array_fill(0, sizeof($this->_getExportAttributes()), null);
			
			$attributeCodes = array_flip($this->_getExportAttributes());
			foreach ($this->_getSearchableAttributes() as $attribute) {
				if (isset($attributeCodes[$attribute->getAttributeCode()]) && !in_array($attribute->getAttributeCode(), array('sku', 'status', 'visibility'))) {
					$this->_exportAttributes[$attributeCodes[$attribute->getAttributeCode()]] = $attribute;
				}
			}
		}
		
		// fill dataArray with the values of the attributes that should be exported
		foreach($this->_exportAttributes AS $pos => $attribute) {
			if ($attribute != null) {
				$value = isset($values[$attribute->getId()]) ? $values[$attribute->getId()] : null;
				$dataArray[$pos] = $this->_getAttributeValue($attribute->getId(), $value, $storeId);
			}
		}
    }
}
