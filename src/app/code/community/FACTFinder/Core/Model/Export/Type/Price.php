<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * This class provides the Price export
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Export_Type_Price extends Mage_Core_Model_Resource_Db_Abstract
    implements FACTFinder_Core_Model_Export_Type_Interface
{

    const FILENAME_PATTERN = 'store_%s_price.csv';
    const FILE_VALIDATOR = 'factfinder/file_validator_price';
    const CSV_DELIMITER = ';';

    /**
     * defines Export Columns
     * @var array
     */
    protected $_exportColumns = array(
        'entity_id',
        'customer_group_id',
        'final_price',
        'min_price',
    );

    /**
     * @var FACTFinder_Core_Model_File
     */
    protected $_file;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setResource('core');
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
     * Get file handler instance for store
     *
     * @param int $storeId
     *
     * @return FACTFinder_Core_Model_File
     *
     * @throws Exception
     */
    protected function _getFile($storeId)
    {
        if (!$this->_file) {
            $dir = Mage::helper('factfinder/export')->getExportDirectory();
            $fileName = $this->getFilenameForStore($storeId);
            $this->_file = Mage::getModel('factfinder/file');
            $this->_file->setValidator(Mage::getModel(self::FILE_VALIDATOR));
            $this->_file->open($dir, $fileName);
        }

        return $this->_file;
    }


    /**
     * Write CSV Row
     *
     * @param array $data
     * @param int $storeId
     *
     * @return bool
     */
    protected function _addCsvRow($data, $storeId = 0)
    {
        return $this->_getFile($storeId)->writeCsv($data, self::CSV_DELIMITER);
    }


    /**
     * Export price data
     *
     * @param int $storeId Store Id
     *
     * @return string|bool
     */
    public function saveExport($storeId = null)
    {
        /** @var FACTFinder_Core_Model_Export_Semaphore $semaphore */
        $semaphore = Mage::getModel('factfinder/export_semaphore');
        $semaphore->setStoreId($storeId)
            ->setType('price');


        try {
            $semaphore->lock();

            $this->_saveExport($storeId);

            $semaphore->release();
        } catch (RuntimeException $e) {
            Mage::helper('factfinder/debug')->log('Export action was locked', true);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            $semaphore->release();
        }

        if (Mage::helper('factfinder/export')->isValidationEnabled($storeId) && !$this->_getFile($storeId)->isValid()) {
            return false;
        }

        return $this->_getFile($storeId)->getPath();
    }


    /**
     * Get prices from Price Index Table
     *
     * @param int $storeId Store ID
     * @param int $part
     * @param int $limit
     *
     * @return array
     */
    protected function _getPrices($storeId, $part = 1, $limit = 100)
    {

        $store = Mage::app()->getStore($storeId);
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog/product_index_price')),
                $this->_exportColumns);

        if ($storeId !== null) {
            $select->where('e.website_id = ?', $store->getWebsiteId());
        }

        $select->limitPage($part, $limit)
            ->order('e.entity_id');

        return $this->_getReadAdapter()->fetchAll($select);
    }


    /**
     * Pre-Generate all price exports for all stores
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
                $price = Mage::getModel('factfinder/export_type_price');
                $filePath = $price->saveExport($id);
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
     * Get number of entries to be exported
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getSize($storeId)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog/product_index_price')),
                new Zend_Db_Expr('count(*)')
            );

        $store = Mage::app()->getStore($storeId);
        if ($storeId !== null) {
            $select->where('e.website_id = ?', $store->getWebsiteId());
        }

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }


    /**
     * Perform export actions and write to file
     *
     * @param int $storeId
     *
     * @return FACTFinder_Core_Model_Export_Type_Price
     */
    protected function _saveExport($storeId)
    {
        $this->_addCsvRow($this->_exportColumns, $storeId);

        $page = 1;
        $stocks = $this->_getPrices($storeId, $page);

        while ($stocks) {
            foreach ($stocks as $stock) {
                $this->_addCsvRow($stock, $storeId);
            }

            $stocks = $this->_getPrices($storeId, ++$page);
        }

        return $this;
    }


}
