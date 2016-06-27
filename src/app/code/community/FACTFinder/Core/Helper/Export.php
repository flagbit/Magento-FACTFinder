<?php
/**
 * Export.php
 *
 * @category Mage
 * @package magento
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Helper_Export extends Mage_Core_Helper_Abstract
{

    const EXPORT_CONFIG_PATH   = 'factfinder/export';
    const FTP_HOST             = 'ftp_host';
    const FTP_PASSWORD         = 'ftp_password';
    const FTP_USER             = 'ftp_user';
    const FTP_PORT             = 'ftp_port';
    const FTP_DIR              = 'ftp_path';
    const FTP_SECURE           = 'ftp_ssl';
    const FTP_ENABLED          = 'use_ftp';
    const IMPORT_DELAY_ENABLED = 'import_delay_enabled';
    const IMPORT_DELAY_KEY     = 'factfinder_import_delay';
    const ARCHIVE_PATTERN      = 'store_%s_export.zip';
    const EXPORT_TRIGGER_DELAY = 90;

    /**
     * @var int
     */
    protected $_storeId = 0;


    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }


    /**
     * Get array of export types
     * The order is normally important
     *
     * @return array
     */
    public function getExportTypes()
    {
        return array(
            'stock',
            'price',
            'product',
        );
    }


    /**
     * Check if FTP upload is enabled for store
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function useFtp($storeId = 0)
    {
        return (bool) $this->getExportConfigValue(self::FTP_ENABLED, $storeId);
    }


    /**
     * Get export config value
     *
     * @param string $field
     * @param int    $storeId
     *
     * @return null|string
     */
    public function getExportConfigValue($field, $storeId)
    {
        if (!$storeId) {
            $storeId = $this->_storeId;
        }

        return Mage::app()->getStore($storeId)->getConfig(self::EXPORT_CONFIG_PATH . '/' . $field);
    }


    /**
     * Get FTP host from config
     *
     * @param int $storeId
     *
     * @return null|string
     */
    public function getFtpHost($storeId = 0)
    {
        return $this->getExportConfigValue(self::FTP_HOST, $storeId);
    }


    /**
     * Get FTP port from config
     *
     * @param int $storeId
     *
     * @return null|string
     */
    public function getFtpPort($storeId = 0)
    {
        return $this->getExportConfigValue(self::FTP_PORT, $storeId);
    }


    /**
     * Get FTP password from config
     *
     * @param int $storeId
     *
     * @return null|string
     */
    public function getFtpPassword($storeId = 0)
    {
        return $this->getExportConfigValue(self::FTP_PASSWORD, $storeId);
    }


    /**
     * Get FTP user from config
     *
     * @param int $storeId
     *
     * @return null|string
     */
    public function getFtpUser($storeId = 0)
    {
        return $this->getExportConfigValue(self::FTP_USER, $storeId);
    }


    /**
     * Get FTP directory from config
     *
     * @param int $storeId
     *
     * @return null|string
     */
    public function getFtpDirectory($storeId = 0)
    {
        return $this->getExportConfigValue(self::FTP_DIR, $storeId);
    }


    /**
     * Check if SSL should be used
     *
     * @param int $storeId
     *
     * @return null|string
     */
    public function getFtpSecure($storeId = 0)
    {
        return $this->getExportConfigValue(self::FTP_SECURE, $storeId);
    }


    /**
     * Get export directory path
     *
     * @return string
     */
    public function getExportDirectory()
    {
        return Mage::getBaseDir('var') . DS . 'factfinder';
    }


    /**
     * Check whether import must be triggered
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function isImportTriggerEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag('factfinder/export/trigger_data_import', $storeId);
    }


    /**
     * Check whether import trigger delay is enabled
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function isImportDelayEnabled($storeId)
    {
        // this usually returns false for WINDOWS,
        // since forking processes is not supported there
        if (!function_exists('pcntl_fork')) {
            return false;
        }

        return (bool) $this->getExportConfigValue(self::IMPORT_DELAY_ENABLED, $storeId);
    }


    /**
     * Create archive with export files for store.
     * No archive will be created if no export files exist.
     *
     * @param int $storeId
     *
     * @return string Archive name, even if it was not created.
     */
    public function archiveFiles($storeId)
    {
        $dir = $this->getExportDirectory();

        $archiveName = sprintf(self::ARCHIVE_PATTERN, $storeId);

        $zip = new ZipArchive();
        $zip->open($dir . DS . $archiveName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        foreach ($this->getExportTypes() as $type) {
            $model = Mage::getModel('factfinder/export_' . $type);
            $filename = $model->getFilenameForStore($storeId);
            $zip->addFile($dir . DS . $filename, $filename);
        }

        $zip->close();

        return $dir . DS . $archiveName;
    }


    /**
     * Get duration of import delay in seconds for import type
     *
     * @param string $type
     *
     * @return int
     */
    public function getImportDelay($type)
    {
        $key = self::IMPORT_DELAY_KEY;
        $delay = Mage::registry($key);

        // if this type of import was already calculated
        $typeKey = self::IMPORT_DELAY_KEY . '_' . $type;
        if (Mage::registry($typeKey)) {
            return Mage::registry($typeKey);
        }

        $delay +=self::EXPORT_TRIGGER_DELAY;

        // save the new base value in registry
        Mage::unregister($key);
        Mage::register($key, $delay);

        // save type
        Mage::register($typeKey, $delay);

        return $delay;
    }


}