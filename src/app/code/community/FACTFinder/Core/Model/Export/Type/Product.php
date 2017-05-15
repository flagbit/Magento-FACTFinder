<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * This class provides the Product export
 *
 * @method FACTFinder_Core_Model_Resource_Product getResource()
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Export_Type_Product extends Mage_Core_Model_Abstract
    implements FACTFinder_Core_Model_Export_Type_Interface
{

    const FILENAME_PATTERN = 'store_%s_product.csv';
    const CSV_DELIMITER = ';';
    const FILE_VALIDATOR = 'factfinder/file_validator_product';

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
    protected $_headerColumns = null;

    /**
     * @var FACTFinder_Core_Model_File
     */
    protected $_file = null;

    /**
     * @var array
     */
    protected $_defaultHeader = array(
        'id',
        'parent_id',
        'sku',
        'category',
        'filterable_attributes',
        'searchable_attributes',
        'numerical_attributes',
    );

    /**
     * Visibility attribute id
     *
     * @var int
     */
    protected $_visibilityId;

    /**
     * Status attribute id
     *
     * @var int
     */
    protected $_statusId;


    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('factfinder/product');
    }


    /**
     * Add row to csv
     *
     * @param array $data    Array of data
     * @param int   $storeId
     *
     * @return FACTFinder_Core_Model_Export_Type_Product
     */
    protected function _writeCsvRow($data, $storeId)
    {
        $this->_getFile($storeId)->writeCsv($data, self::CSV_DELIMITER);

        return $this;
    }


    /**
     * Get export filename for store
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getFilenameForStore($storeId)
    {
        return sprintf(self::FILENAME_PATTERN, $storeId);
    }


    /**
     * @return string
     */
    public function getExportDirectory()
    {
        return $this->getHelper()->getExportDirectory();
    }


    /**
     * Get CSV Header Array
     *
     * @param int $storeId
     *
     * @return array
     */
    protected function _getHeader($storeId = 0)
    {
        if (!isset($this->_headerColumns[$storeId])) {
            $additionalColumns = array();
            if (Mage::getStoreConfigFlag('factfinder/export/urls', $storeId)) {
                $additionalColumns[] = 'image';
            }
            $additionalColumns[] = 'deeplink';

            $exportAttributes = $this->getAttributeModel()->getExportAttributes($storeId);

            $this->_headerColumns[$storeId] = array_merge(
                $this->_defaultHeader,
                $additionalColumns,
                $exportAttributes
            );

            // apply field limit as required by ff
            if(count($this->_headerColumns[$storeId]) > 128) {
                array_splice($this->_headerColumns[$storeId], 128);
            }
        }


        return $this->_headerColumns[$storeId];
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
            if (!Mage::helper('factfinder')->isEnabled(null, $id)) {
                continue;
            }

            try {
                $filePath = $this->saveExport($id);
                if ($filePath) {
                    $paths[] = $filePath;
                }
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
        $path = '';

        /** @var FACTFinder_Core_Model_Export_Semaphore $semaphore */
        $semaphore = Mage::getModel('factfinder/export_semaphore');
        $semaphore->setStoreId($storeId)
            ->setType('product');

        try {
            $semaphore->lock();

            $path = $this->doExport($storeId);

            $semaphore->release();
        } catch (RuntimeException $e) {
            Mage::helper('factfinder/debug')->log('Export action was locked', true);
        } catch (Exception $e) {
            Mage::logException($e);
            $semaphore->release();
        }

        return $path;
    }


    /**
     * Get file handler instance for store
     *
     * @param int $storeId
     *
     * @return \FACTFinder_Core_Model_File
     *
     * @throws \Exception
     */
    protected function _getFile($storeId)
    {
        if (!isset($this->_file[$storeId])) {
            $dir = $this->getExportDirectory();
            $fileName = $this->getFilenameForStore($storeId);

            $file = Mage::getModel('factfinder/file');

            if ($this->getHelper()->isValidationEnabled($storeId)) {
                $file->setValidator(Mage::getModel(self::FILE_VALIDATOR));
            }

            $file->open($dir, $fileName);

            $this->_file[$storeId] = $file;
        }

        return $this->_file[$storeId];
    }


    /**
     * Export Product Data with Attributes
     * direct Output as CSV
     *
     * @param int $storeId Store View Id
     *
     * @return string|bool
     */
    public function doExport($storeId = null)
    {
        $this->_resetInternalState();

        $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();

        $header = $this->_getHeader($storeId);
        $this->_writeCsvRow($header, $storeId);

        $staticFields = $this->getAttributeModel()->getStaticFields($storeId);
        $dynamicFields = $this->getAttributeModel()->getDynamicFields($storeId);

        $lastProductId = 0;
        while (true) {
            // get basic product data
            $products = $this->getResource()->getSearchableProducts($storeId, $staticFields, $lastProductId);
            if (!$products) {
                break;
            }

            $productIds = array();
            $productRelations = array();
            foreach ($products as $productData) {
                $productId = $productData['entity_id'];
                $productIds[] = $productId;
                $productChildren = $this->getResource()->getProductChildIds($productId, $productData['type_id']);
                foreach ($productChildren as $child) {
                    $productIds[] = $child['entity_id'];
                }
                $productRelations[$productId] = $productChildren;
                $lastProductId = $productId;
            }

            $attributeValues = $this->getAttributeModel()
                ->getProductAttributes($storeId, $productIds, $dynamicFields); // store, ids, codes

            foreach ($products as $productData) {
                if ($this->shouldSkipProduct($attributeValues, $productData)) {
                    continue;
                }

                $productId = $productData['entity_id'];

                $productAttributes = $attributeValues[$productId];

                $categoryPath = $this->_getCategoryPath($productId, $storeId);

                if ($categoryPath == '' && !$this->_isExportProductsWithoutCategories($storeId)) {
                    continue;
                }

                $productIndex = array(
                    $productId,
                    $productData[$idFieldName],
                    $productData['sku'],
                    $categoryPath,
                    $this->_formatAttributes('filterable', $productAttributes, $storeId),
                    $this->_formatAttributes('searchable', $productAttributes, $storeId),
                    $this->_formatAttributes('numerical', $productAttributes, $storeId),
                );

                $productIndex = $this->_exportImageAndDeepLink($productIndex, $productData, $storeId);
                $productIndex = $this->getAttributeModel()
                    ->addAttributesToRow($productIndex, $productAttributes, $storeId, $productData);

                $this->_writeCsvRow($productIndex, $storeId);

                $this->_exportChildren($storeId, $productRelations, $attributeValues, $productData);
            }
        }

        if (!$this->_getFile($storeId)->isValid()) {
            return false;
        }
        
        Mage::dispatchEvent('factfinder_export_after', array(
            'store_id' => $storeId,
            'file'     => $this->_getFile($storeId),
        ));

        return $this->_getFile($storeId)->getPath();
    }


    /**
     * Resets the internal state of this export.
     */
    protected function _resetInternalState()
    {
        $this->_categoryNames = null;
        $this->_productsToCategoryPath = null;
    }


    /**
     * Get Category Path by Product ID
     *
     * @param int $productId
     * @param int $storeId
     *
     * @return string
     */
    protected function _getCategoryPath($productId, $storeId = null)
    {

        if ($this->_categoryNames === null) {
            $this->_categoryNames = $this->getResource()->getCategoryNames($storeId);
        }

        if ($this->_productsToCategoryPath === null) {
            $this->_productsToCategoryPath = $this->getResource()->getCategoryPaths($storeId);
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
     * Add image and deep link information to product row
     *
     * @param array $productIndex
     * @param array $productData
     * @param int   $storeId
     *
     * @return array
     */
    protected function _exportImageAndDeepLink($productIndex, $productData, $storeId)
    {
        $helper = $this->getHelper();

        // emulate store
        $oldStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore($storeId);

        if ($helper->shouldExportImages($storeId)) {
            $productIndex[] = $this->getProductImageUrl($productData['entity_id'], $storeId);
        }
        $productIndex[] = $this->getProductUrl($productData['entity_id'], $storeId);

        // finish emulation
        Mage::app()->setCurrentStore($oldStore);

        return $productIndex;
    }


    /**
     * Check if products without categories should be exported
     *
     * @param int $storeId
     *
     * @return bool
     */
    protected function _isExportProductsWithoutCategories($storeId)
    {
        return Mage::getStoreConfig('factfinder/export/products_without_categories', $storeId);
    }


    /**
     * Get export helper
     *
     * @return FACTFinder_Core_Helper_Export
     */
    protected function getHelper()
    {
        return Mage::helper('factfinder/export');
    }


    /**
     * Gte product URL for store
     *
     * @param int $productId
     * @param int $storeId
     *
     * @return string
     */
    protected function getProductUrl($productId, $storeId)
    {
        $productUrl = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('entity_id', $productId)
            ->setStoreId($storeId)
            ->setPage(1, 1)
            ->addUrlRewrite()
            ->getFirstItem()
            ->getProductUrl();

        return $productUrl;
    }


    /**
     * Get image URL for product
     *
     * @param int $productId
     * @param int $storeId
     *
     * @return string
     */
    protected function getProductImageUrl($productId, $storeId)
    {
        $helper = $this->getHelper();

        $imageType = $helper->getExportImageType();
        $imageBaseFile = Mage::getResourceSingleton('catalog/product')
            ->getAttributeRawValue($productId, $imageType, $storeId);

        /** @var Mage_Catalog_Model_Product_Image $imageModel */
        $imageModel = Mage::getModel('catalog/product_image');

        // if size was set
        if ($helper->getExportImageWidth($storeId)) {
            $imageModel
                ->setWidth($helper->getExportImageWidth($storeId))
                ->setHeight($helper->getExportImageHeight($storeId));
        }

        $imageModel
            ->setDestinationSubdir($imageType)
            ->setBaseFile($imageBaseFile);

        // if no cache image was generated we should create one
        if (!$imageModel->isCached()) {
            try {
                $imageModel
                    ->resize()
                    ->saveFile();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $imageModel->getUrl();
    }


    /**
     * Get number of products that should be exported
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getSize($storeId)
    {
        $staticFields = $this->getAttributeModel()->getStaticFields($storeId);
        $dynamicFields = $this->getAttributeModel()->getDynamicFields($storeId);

        $count = 0;

        $lastId = 0;
        while (true) {
            $products = $this->getResource()->getSearchableProducts($storeId, $staticFields, $lastId);
            if (!$products) {
                break;
            }

            $attributes = array();
            $relations = array();
            foreach ($products as $productData) {
                $attributes[$productData['entity_id']] = $productData['entity_id'];
                $children = $this->getResource()->getProductChildIds($productData['entity_id'], $productData['type_id']);
                $relations[$productData['entity_id']] = $children;
                if ($children) {
                    foreach ($children as $child) {
                        $attributes[$child['entity_id']] = $child;
                    }
                }
            }

            $lastId = $productData['entity_id'];

            $attributes = $this->getAttributeModel()
                ->getProductAttributes($storeId, array_keys($attributes), $dynamicFields);
            foreach ($products as $productData) {

                if ($this->shouldSkipProduct($attributes, $productData)) {
                    continue;
                }

                $categoryPath = $this->_getCategoryPath($productData['entity_id'], $storeId);

                if ($categoryPath == '' && !$this->_isExportProductsWithoutCategories($storeId)) {
                    continue;
                }

                $count++;

                $children = $relations[$productData['entity_id']];
                foreach ($children as $child) {
                    if (isset($attributes[$child['entity_id']])) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }


        /**
     * Check if product should be skipped in export
     *
     * @param $attributes
     * @param $productData
     *
     * @return bool
     */
    protected function shouldSkipProduct($attributes, $productData)
    {
        if (!isset($attributes[$productData['entity_id']])) {
            return true;
        }

        // status and visibility filter
        if (!$this->_visibilityId && !$this->_statusId) {
            $this->_visibilityId = $this->getAttributeModel()->getSearchableAttribute('visibility')->getId();
            $this->_statusId = $this->getAttributeModel()->getSearchableAttribute('status')->getId();
        }

        $visibilities = Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
        $statuses = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();

        $productAttributes = $attributes[$productData['entity_id']];

        if (!isset($productAttributes[$this->_visibilityId])
            || !isset($productAttributes[$this->_statusId])
            || !in_array($productAttributes[$this->_visibilityId], $visibilities)
            || !in_array($productAttributes[$this->_statusId], $statuses)
        ) {
            return true;
        }

        return false;
    }


    /**
     * Get instance of attribute model
     *
     * @return FACTFinder_Core_Model_Export_Type_Product_Attribute
     */
    protected function getAttributeModel()
    {
        return Mage::getSingleton('factfinder/export_type_product_attribute');
    }


    /**
     * Export product children
     *
     * @param int   $storeId
     * @param array $productRelations
     * @param array $attributeValues
     * @param array $productData
     *
     * @return void
     */
    protected function _exportChildren($storeId, $productRelations, $attributeValues, $productData)
    {
        $idFieldName = Mage::helper('factfinder/search')->getIdFieldName();
        $productChildren = $productRelations[$productData['entity_id']];
        foreach ($productChildren as $productChild) {
            if (!isset($attributeValues[$productChild['entity_id']])) {
                continue;
            }

            $productAttributes = $attributeValues[$productChild['entity_id']];

            $subProductIndex = array(
                $productChild['entity_id'],
                $productData[$idFieldName],
                $productChild['sku'],
                $this->_getCategoryPath($productData['entity_id'], $storeId),
                $this->_formatAttributes('filterable', $productAttributes, $storeId),
                $this->_formatAttributes('searchable', $productAttributes, $storeId),
                $this->_formatAttributes('numerical', $productAttributes, $storeId),
            );

            //no need to add image and deeplink to child product, just add empty values
            if ($this->getHelper()->shouldExportImages($storeId)) {
                $subProductIndex[] = '';
            }
            $subProductIndex[] = '';

            $subProductIndex = $this->getAttributeModel()->addAttributesToRow(
                $subProductIndex,
                $attributeValues[$productChild['entity_id']],
                $storeId,
                $productData
            );

            $this->_writeCsvRow($subProductIndex, $storeId);
        }
    }


    /**
     * Format attributes
     * Method wrapper
     *
     * @param $type
     * @param $attributes
     * @param $storeId
     *
     * @return string
     */
    protected function _formatAttributes($type, $attributes, $storeId)
    {
        return $this->getAttributeModel()->formatAttributes($type, $attributes, $storeId);
    }


}
