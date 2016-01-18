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
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_path;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_file = new Varien_Io_File();
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
        $this->_path = $dir . DS . $filename;

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
     * Returns path of current file
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Close stream
     *
     * @return bool
     */
    public function close()
    {
        return $this->_file->streamClose();
    }


    /**
     * Class destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
