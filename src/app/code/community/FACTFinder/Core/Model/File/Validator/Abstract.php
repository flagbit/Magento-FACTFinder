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
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
abstract class FACTFinder_Core_Model_File_Validator_Abstract
{
    const DEFAULT_CSV_DELIMITER = ',';

    const EXCEPTION_CSV_FIELDS_MISMATCH = 'Number of fields in row %s does not match number fields in the header';
    const EXCEPTION_FILE_IS_EMPTY = 'File is empty!';


    /**
     * Check if the file is valid or not
     *
     * @param $file
     *
     * @return bool
     */
    abstract public function validate($file);


    /**
     * Check if all rows have the same number of records
     *
     * @param $file
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function checkCsvConsistency($file)
    {
        $file = fopen($file, 'r');

        $headerSize = count(fgetcsv($file, null, $this->getCsvDelimiter()));

        $rowNumber = 1;
        while ($row = fgetcsv($file, null, $this->getCsvDelimiter())) {
            $rowNumber++;
            if (!empty($row) && count($row) != $headerSize) {
                throw new Exception(sprintf(self::EXCEPTION_CSV_FIELDS_MISMATCH, $rowNumber));
            }
        }
    }


    /**
     * Return CSV delimiter that should be used
     *
     * @return string
     */
    protected function getCsvDelimiter()
    {
        return self::DEFAULT_CSV_DELIMITER;
    }


    /**
     * Check if file is not empty
     *
     * @param $file
     *
     * @throws Exception
     */
    protected function checkIfEmpty($file)
    {
        if (!filesize($file)) {
            throw new Exception(self::EXCEPTION_FILE_IS_EMPTY);
        };
    }


}