<?php
interface FACTFinder_Core_Model_Export_Type_Interface
{
    const FILE_VALIDATOR = '';

    /**
     * Export file for store. Return file path or false
     *
     * @param int|null $storeId
     *
     * @return string|false
     */
    public function saveExport($storeId);


    /**
     * Save all stores
     * Return array of file paths
     *
     * @return array
     */
    public function saveAll();


    /**
     * Get expected number of rows to export
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getSize($storeId);


    /**
     * Get export filename for store
     *
     * @param $storeId
     *
     * @return string
     */
    public function getFilenameForStore($storeId);


    /**
     * Check if export type is enabled
     *
     * @return bool
     */
    public function isEnabled();


}