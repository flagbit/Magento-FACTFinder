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
 * This class provides the stock export
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Export_Type_Stock extends Mage_Core_Model_Resource_Db_Abstract
    implements FACTFinder_Core_Model_Export_Type_Interface
{

    const FILENAME_PATTERN = 'store_%s_stock.csv';
    const FILE_VALIDATOR = 'factfinder/file_validator_stock';
    const CSV_DELIMITER = ';';

    /**
     * defines Export Columns
     *
     * @var array
     */
    protected $_exportColumns = array(
        'product_id',
        'qty',
        'stock_status'
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

            if (Mage::helper('factfinder/export')->isValidationEnabled($storeId)) {
                $this->_file->setValidator(Mage::getModel(self::FILE_VALIDATOR));
            }

            $this->_file->open($dir, $fileName);
        }

        return $this->_file;
    }


    /**
     * Write CSV Row
     *
     * @param array $data
     * @param int   $storeId
     *
     * @return bool
     */
    protected function _addCsvRow($data, $storeId = 0)
    {
        return $this->_getFile($storeId)->writeCsv($data, self::CSV_DELIMITER);
    }


    /**
     * Export Stock Data
     *
     * @param int $storeId Store ID
     *
     * @return bool|string
     */
    public function saveExport($storeId = null)
    {
        /** @var FACTFinder_Core_Model_Export_Semaphore $semaphore */
        $semaphore = Mage::getModel('factfinder/export_semaphore');
        $semaphore->setStoreId($storeId)
            ->setType('stock');

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

        if (!$this->_getFile($storeId)->isValid()) {
            return false;
        }

        return $this->_getFile($storeId)->getPath();
    }


    /**
     * Get Stocks from Stock Index Table
     *
     * @param int $storeId Store ID
     * @param int $part
     * @param int $limit
     *
     * @return array
     */
    protected function _getStockData($storeId, $part = 1, $limit = 100)
    {
        $store  = Mage::app()->getStore($storeId);
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('cataloginventory/stock_status')),
                $this->_exportColumns
            );

        if ($storeId !== null) {
            $select->where('e.website_id = ?', $store->getWebsiteId());
        }

        $select->limitPage($part, $limit)
            ->order('e.product_id');

        return $this->_getReadAdapter()->fetchAll($select);
    }


    /**
     * Pre-Generate all stock exports for all stores
     *
     * @return array
     */
    public function saveAll()
    {
        $paths = array();
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            if (!Mage::helper('factfinder')->isEnabled(null, $store->getId())) {
                continue;
            }

            try {
                /** @var FACTFinder_Core_Model_Export_Type_Stock $stock */
                $stock = Mage::getModel('factfinder/export_type_stock');
                $filePath = $stock->saveExport($store->getId());
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
     * Get number of rows to be exported
     *
     * @param $storeId
     *
     * @return int
     */
    public function getSize($storeId)
    {
        $store  = Mage::app()->getStore($storeId);
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('cataloginventory/stock_status')),
                new Zend_Db_Expr('count(*)')
            );

        if ($storeId !== null) {
            $select->where('e.website_id = ?', $store->getWebsiteId());
        }

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }


    /**
     * Perform export action and try to write that to file
     *
     * @param int $storeId
     *
     * @return FACTFinder_Core_Model_Export_Type_Stock
     */
    protected function _saveExport($storeId)
    {
        $this->_addCsvRow($this->_exportColumns, $storeId);

        $page = 1;
        $stocks = $this->_getStockData($storeId, $page);

        while ($stocks) {
            foreach ($stocks as $stock) {
                $this->_addCsvRow($stock, $storeId);
            }

            $stocks = $this->_getStockData($storeId, ++$page);
        }

        return $this;
    }


}
