<?php
/**
 * Observer.php
 *
 * @category Mage
 * @package magento
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Export_Observer
{

    public function __construct()
    {
        $this->_initFFAutoloader();
    }

    /**
     * Init fact-finder lib autoloader
     *
     * @return void
     */
    protected function _initFFAutoloader()
    {
        $autoloaderClass = new FACTFinder_Core_Model_Autoloader();
        $autoloaderClass->addAutoloader(new Varien_Event_Observer());
    }

    /**
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function triggerImportAfterExport($observer)
    {
        $file = $observer->getFile();

        if (!$file instanceof FACTFinder_Core_Model_File || !$file->isValid()) {
            return;
        }

        $helper = Mage::helper('factfinder');
        $storeId = $observer->getStoreId();

        $this->uploadFileToFtp($observer);

        if (Mage::helper('factfinder/export')->isImportTriggerEnabled($storeId)) {
            $channel = $helper->getPrimaryChannel($storeId);
            $download = !Mage::helper('factfinder/export')->useFtp($storeId);
            $facade = Mage::getModel('factfinder/facade');
            $facade->setStoreId($storeId);
            $facade->triggerDataImport($channel, $download);
        }
    }


    /**
     * Archive and export files and upload them to FTP
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    protected function uploadFileToFtp($observer)
    {
        $helper = Mage::helper('factfinder/export');
        $storeId = $observer->getStoreId();

        if (!$helper->useFtp($storeId)) {
            return;
        }

        $file = $helper->archiveFiles($storeId);

        if (!is_file($file)) {
            return;
        }

        $helper->setStoreId($storeId);

        try {
            $ftp = Mage::getModel('factfinder/ftp', array(
                $helper->getFtpHost(),
                $helper->getFtpPort(),
                $helper->getFtpSecure()
            ));
            $ftp->login($helper->getFtpUser(), $helper->getFtpPassword())
                ->chDir($helper->getFtpDirectory())
                ->upload($file)
                ->close();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
     * Export all types for all stores
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function exportAll($observer)
    {
        foreach (Mage::helper('factfinder/export')->getExportTypes() as $type => $model) {
            $model->saveAll();
        }
    }


}