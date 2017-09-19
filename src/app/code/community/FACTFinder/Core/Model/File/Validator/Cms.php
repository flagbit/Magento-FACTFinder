<?php
class FACTFinder_Core_Model_File_Validator_Cms extends FACTFinder_Core_Model_File_Validator_Abstract
{


    /**
     * Validate file
     *
     * @param string $file
     *
     * @return bool
     */
    public function validate($file)
    {
        $result = true;

        try {
            $this->checkIfEmpty($file);
            $this->checkCsvConsistency($file);

            $storeId = $this->getStoreIdFromFile($file, FACTFinder_Core_Model_Export_Type_Cms::FILENAME_PATTERN);
            $expectedSize = Mage::getModel('factfinder/export_type_cms')->getSize($storeId);

            $this->checkNumberOfLines($file, $expectedSize);
        } catch (Exception $e) {
            $this->logException($file, $e);
            $result = false;
        }

        return $result;
    }


    /**
     * @return string
     */
    protected function getCsvDelimiter()
    {
        return FACTFinder_Core_Model_Export_Type_CMS::CSV_DELIMITER;
    }
}