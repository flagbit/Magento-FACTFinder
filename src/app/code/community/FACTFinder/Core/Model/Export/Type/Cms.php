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
 * This class provides the cms page export
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Export_Type_Cms implements FACTFinder_Core_Model_Export_Type_Interface
{

    const FILENAME_PATTERN = 'store_%s_cms.csv';
    const FILE_VALIDATOR = 'factfinder/file_validator_cms';
    const CSV_DELIMITER = ';';

    /**
     * defines Export Columns
     *
     * @var array
     */
    protected $_exportColumns = array(
        'page_id',
        'identifier',
        'title',
        'content_heading',
        'content',
        'link',
        'image',
    );

    /**
     * @var FACTFinder_Core_Model_File
     */
    protected $_file;


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

            if (Mage::helper('factfinder/export')->isValidationEnabled($storeId) ) {
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
            ->setType('cms');

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

        return $this->_getFile($storeId)->getPath();
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
                /** @var FACTFinder_Core_Model_Export_Type_Cms $cmsExport */
                $cmsExport = Mage::getModel('factfinder/export_type_cms');
                $filePath = $cmsExport->saveExport($store->getId());
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
        // no validation needed
        return 0;
    }


    /**
     * Perform export action and try to write that to file
     *
     * @param int $storeId
     *
     * @return FACTFinder_Core_Model_Export_Type_Cms
     */
    protected function _saveExport($storeId)
    {
        $this->_addCsvRow($this->_exportColumns, $storeId);

        /** @var Mage_Cms_Model_Resource_Page_Collection $cmsCollection */
        $cmsCollection = Mage::getModel('cms/page')->getCollection();
        $cmsCollection->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1);

        foreach ($cmsCollection as $page) {
            if ($page->getSkipFfExport()) {
                continue;
            }

            $row = $this->getCmsData($page, $storeId);
            $this->_addCsvRow($row, $storeId);
        }

        return $this;
    }


    /**
     * Check if export type is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::helper('factfinder/export')->isCmsExportEnabled();
    }


    /**
     * Get data from CMS page
     *
     * @param Mage_Cms_Model_Page $page
     * @param int                 $storeId
     *
     * @return array
     */
    private function getCmsData($page, $storeId)
    {
        $row = array(
            $page->getId(),
            $page->getIdentifier(),
            $page->getTitle(),
            $page->getContentHeading(),
            html_entity_decode(strip_tags($page->getContent())),
            Mage::getModel('core/url')->getUrl($page->getIdentifier(), array('_store' => $storeId)),
            $this->findFirstImageInContent($page->getContent(), $storeId),
        );

        return $row;
    }


    /**
     * Parse content to find first image link in it
     *
     * @param string $content
     * @param        $storeId
     *
     * @return string
     */
    private function findFirstImageInContent($content, $storeId)
    {
        $pattern = '/(http:\/\/|https:\/\/)[a-zA-Z0-9\.\/_]+\.(jpg|png)/';
        $matches = array();
        preg_match($pattern, $content, $matches);

        if (isset($matches[0])) {
            return $matches[0];
        }

        $skinPattern = '/{{skin\surl=\'([a-zA-Z0-9_\/\.]+)\'}}/';
        preg_match($skinPattern, $content, $matches);

        if (isset($matches[1])) {
            return Mage::getDesign()->getSkinUrl($matches[1], array('_store' => $storeId));
        }

        return '';
    }

}
