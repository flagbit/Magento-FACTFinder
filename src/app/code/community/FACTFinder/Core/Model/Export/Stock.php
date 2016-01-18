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
            $dir = Mage::getBaseDir('var') . DS . 'factfinder';
            $fileName = 'store_' . $storeId . '_stock.csv';
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
        foreach($data as &$item){
            $item = str_replace(array("\r", "\n", "\""), ' ', addcslashes(strip_tags($item), '"'));
        }

        $line =  '"'.implode('";"', $data).'"'."\n";

        return $this->_getFile($storeId)->write($line);
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

        return $this->_getFile()->getPath();
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


}
