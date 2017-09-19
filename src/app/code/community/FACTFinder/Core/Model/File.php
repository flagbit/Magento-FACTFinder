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
class FACTFinder_Core_Model_File
{
    const BACKUP_DIR = 'bak';

    /**
     * @var Varien_Io_File
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_currentPath;

    /**
     * @var bool
     */
    protected $_useTmpFile = true;

    /**
     * @var string
     */
    protected $_dir;

    /**
     * @var string
     */
    protected $_filename;


    /**
     * @var FACTFinder_Core_Model_File_Validator_Abstract
     */
    protected $_validator;


    /**
     * @var null|bool
     */
    protected $_isValid = null;


    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_file = new Varien_Io_File();
    }


    /**
     * Set validator object
     *
     * @param FACTFinder_Core_Model_File_Validator_Abstract $validator
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setValidator(FACTFinder_Core_Model_File_Validator_Abstract $validator)
    {
        if (!$validator instanceof FACTFinder_Core_Model_File_Validator_Abstract) {
            throw new Exception('Validator must be an instance of FACTFinder_Core_Model_File_Validator_Abstract!');
        }

        $this->_validator = $validator;

        return $this;
    }


    /**
     * Set if a temporary file should be used
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setUseTmpFile($value)
    {
        $this->_useTmpFile = $value;

        return $this;
    }


    /**
     * Make directory
     *
     * @param string $dir
     * @param int    $mode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function mkdir($dir, $mode = 0777, $recursive=true)
    {
        return $this->_file->mkdir($dir, $mode, $recursive);
    }


    /**
     * Open file for writing
     *
     * @param string $dir
     * @param string $filename
     * @param string $mode
     *
     * @return bool
     */
    public function open($dir, $filename, $mode = 'w+')
    {
        $this->_filename = $filename;
        $this->_dir = $dir;

        if ($this->_useTmpFile) {
            $filename = $this->getTmpFilename($filename);
        }

        $this->_currentPath = $dir . DS . $filename;

        $this->_file->mkdir($dir);
        $this->_file->open(array('path' => $dir));

        return $this->_file->streamOpen($filename, $mode);
    }


    /**
     * Write a line to the file
     *
     * @param string $str
     *
     * @return bool
     */
    public function write($str)
    {
        return $this->_file->streamWrite($str);
    }


    /**
     * Write array as comma separated values to file
     *
     * @param array  $data
     * @param string $delimiter
     * @param string $enclosure
     *
     * @return bool|int
     */
    public function writeCsv(array $data, $delimiter = ',', $enclosure = '"')
    {
        // sanitize data
        foreach ($data as &$row) {
            $row = str_replace(array("\r", "\n", "\\{$enclosure}"), array(" ", " ", $enclosure), $row);
        }

        return $this->_file->streamWriteCsv($data, $delimiter, $enclosure);
    }

    /**
     * Returns "proper" path of current file
     * If a tmp file is used, this still gives the name of the target file
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_dir . DS . $this->_filename;
    }


    /**
     * Close stream
     *
     * @return bool
     */
    public function close()
    {
        $success = $this->_file->streamClose();

        if ($this->_useTmpFile) {
            $this->_processTmpFile();
        }

        return $success;
    }


    /**
     * Class destructor
     */
    public function __destruct()
    {
        try {
            $this->close();
        } catch(Exception $e) {
            echo PHP_EOL . $e->getMessage() . PHP_EOL;
        }
    }



    /**
     * Get name for temporary file
     *
     * @param $filename
     *
     * @return string
     */
    protected function getTmpFilename($filename)
    {
        $filename .= sprintf('.%s%s', 'tmp', time());

        return $filename;
    }


    /**
     * Check if file is valid
     *
     * @return bool
     */
    public function isValid()
    {
        if (!$this->_validator) {
            return true;
        }

        if ($this->_isValid === null) {
            $this->_isValid = $this->_validator->validate($this->_currentPath);
        }

        return $this->_isValid;
    }


    /**
     * Remove the current file
     *
     * @return FACTFinder_Core_Model_File
     */
    public function moveToBackup()
    {
        $this->_file->mkdir($this->getBackupPath());
        $this->_file->mv($this->_currentPath, $this->getBackupPath() . DS . basename($this->_currentPath));

        return $this;
    }


    /**
     * Get backup directory for invalid files
     *
     * @return string
     */
    protected function getBackupPath()
    {
        return $this->_dir . DS . self::BACKUP_DIR;
    }


    /**
     * Rename the temporary file to the regular name and replace the existing file,
     * if it already exists
     *
     * @return $this
     */
    protected function _processTmpFile()
    {
        if (stripos(PHP_OS, 'win') === 0) {
            sleep(1); // workaround for windows
        }


        if ($this->isValid()) {
            $this->_file->mv($this->_currentPath, $this->getPath());
        } else {
            $this->moveToBackup();
        }

        $this->_currentPath = $this->getPath();

        return $this;
    }
}
