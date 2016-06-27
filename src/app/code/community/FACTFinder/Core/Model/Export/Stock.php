<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Export_Stock extends Mage_Core_Model_Resource_Db_Abstract
{

    const FILENAME_PATTERN = 'store_%s_stock.csv';

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
        return $this->_getFile($storeId)->writeCsv($data, ';');
    }


    /**
     * export Stock Data
     * Write the data to file
     *
     * @param int $storeId Store Id
     *
     * @return $this
     */
    public function saveExport($storeId = null)
    {
        $this->_addCsvRow($this->_exportColumns, $storeId);

        $page = 1;
        $stocks = $this->_getStockData($storeId, $page);

        while ($stocks) {
            foreach($stocks as $stock){
                $this->_addCsvRow($stock, $storeId);
            }

            $stocks = $this->_getStockData($storeId, $page++);
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
        $select = $this->_getWriteAdapter()->select()
            ->from(
                array('e' => $this->getTable('cataloginventory/stock_status')),
                $this->_exportColumns
            );

        if ($storeId !== null) {
            $select->where('e.website_id = ?', $store->getWebsiteId());
        }

        $select->limitPage($part, $limit)
            ->order('e.product_id');

        return $this->_getWriteAdapter()->fetchAll($select);
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
            try {
                $stock = Mage::getModel('factfinder/export_stock');
                $paths[] = $stock->saveExport($store->getId());
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $paths;
    }


}
