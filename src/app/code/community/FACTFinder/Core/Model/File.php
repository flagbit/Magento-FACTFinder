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
class FACTFinder_Core_Model_File
{

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
     * Class constructor
     */
    public function __construct()
    {
        $this->_file = new Varien_Io_File();
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
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function open($dir, $filename)
    {
        $this->_filename = $filename;
        $this->_dir = $dir;

        if ($this->_useTmpFile) {
            $filename = $this->getTmpFilename($filename);
        }

        $this->_currentPath = $dir . DS . $filename;

        $this->_file->mkdir($dir);
        $this->_file->open(array('path' => $dir));

        return $this->_file->streamOpen($filename);
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
        // rename the temporary file to the regular name and replace the existing file, it it already exists
        if ($this->_useTmpFile) {
            rename($this->_currentPath, $this->getPath());
        }

        return $this->_file->streamClose();
    }


    /**
     * Class destructor
     */
    public function __destruct()
    {
        $this->close();
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


}
