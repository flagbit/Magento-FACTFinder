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
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
abstract class FACTFinder_Core_Model_File_Validator_Abstract
{
    const DEFAULT_CSV_DELIMITER = ',';

    const EXCEPTION_CSV_FIELDS_MISMATCH = 'Number of fields in row %s does not match number fields in the header';
    const EXCEPTION_SIZE_MISMATCH = 'The actual number of CSV lines does not match the expected one';
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
                throw new RuntimeException(sprintf(self::EXCEPTION_CSV_FIELDS_MISMATCH, $rowNumber));
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


    /**
     * Get store ID based on filename and file pattern
     *
     * @param $file
     * @param $filenamePattern
     *
     * @return int
     */
    protected function getStoreIdFromFile($file, $filenamePattern)
    {
        $filenamePattern = str_replace('%s', '([\d]+)', $filenamePattern);
        if (preg_match("/$filenamePattern/", basename($file), $matches)) {
            return $matches[1];
        }

        return 0;
    }


    /**
     * Check if the file contains the expected number of lines
     *
     * @param string $file
     * @param int    $expectedSize
     *
     * @throws Exception
     */
    protected function checkNumberOfLines($file, $expectedSize)
    {
        $actualSize = 0;
        $h = fopen($file, 'r');
        while (fgetcsv($h, null, $this->getCsvDelimiter())) {
            $actualSize++;
        }

        // one extra line for the header
        if ($actualSize - $expectedSize !== 1) {
            throw new Exception(self::EXCEPTION_SIZE_MISMATCH);
        }
    }


    /**
     * Process exception
     *
     * @param           $file
     * @param Exception $exception
     *
     * @return void
     */
    protected function logException($file, Exception $exception)
    {
        $helper = Mage::helper('factfinder');

        // log exception as normally
        Mage::logException($exception);

        $message = $helper->__('Invalid FACT-Finder export file detected: %s', basename($file));
        $message .= " | " . $exception->getMessage();

        // add an admin notification
        $notification = Mage::getModel('adminnotification/inbox');
        $notification->setData(
            array(
                'severity'    => 1,
                'title'       => $helper->__('FACTFINDER EXPORT TROUBLES'),
                'description' => $message,
                'url'         => '',
            )
        );
        $notification->save();
    }


}