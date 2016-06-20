<?php
/**
 * Ftp.php
 *
 * @category Mage
 * @package magento
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license GPL
 * @link http://www.flagbit.de
 */

class FACTFinder_Core_Model_Ftp
{

    /**
     * @var null|resource
     */
    protected $_connection = null;


    /**
     * @param string $host
     * @param int    $port
     * @param bool  $secure
     */
    public function __construct($host, $port = 21, $secure = false)
    {
        if (is_array($host)) {
            list($host, $port, $secure) = $host;
        }

        $this->connect($host, $port, $secure);
    }


    /**
     * Create a connection to host
     *
     * @param string $host
     * @param int    $port
     * @param bool   $secure
     *
     * @return $this
     * @throws \Mage_Core_Exception
     */
    public function connect($host, $port = 21, $secure = false)
    {
        $port = (int) $port;
        if ($secure) {
            $this->_connection = ftp_ssl_connect($host, $port);
        } else {
            $this->_connection = ftp_connect($host, $port);
        }

        if (!$this->_connection) {
            Mage::throwException('Unable to connect to FTP host');
        }

        return $this;
    }


    /**
     * Login to an already created connection
     *
     * @param string $user
     * @param string $password
     *
     * @return $this
     *
     * @throws \Mage_Core_Exception
     */
    public function login($user, $password)
    {
        $result = ftp_login($this->_connection, $user, $password);

        if (!$result) {
            Mage::throwException('Unable to login to FTP host');
        }

        return $this;
    }


    /**
     * Upload file to remote host
     *
     * @param string $file
     * @param string $remoteFile
     *
     * @return $this
     *
     * @throws \Mage_Core_Exception
     */
    public function upload($file, $remoteFile = '')
    {
        if (!$remoteFile) {
            $remoteFile = pathinfo($file, PATHINFO_BASENAME);
        }

        $result = ftp_put($this->_connection, $remoteFile, $file, FTP_BINARY);

        if (!$result) {
            Mage::throwException('Unable to upload file to FTP');
        }

        return $this;
    }


    /**
     * Change directory on remote host
     *
     * @param string $path
     *
     * @return $this
     *
     * @throws \Mage_Core_Exception
     */
    public function chDir($path)
    {
        $result = ftp_chdir($this->_connection, $path);

        if (!$result) {
            Mage::throwException('Unable to change FTP directory');
        }

        return $this;
    }


    /**
     * Close existing connection
     *
     * @return $this
     */
    public function close()
    {
        if ($this->_connection) {
            @ftp_close($this->_connection);
        }

        return $this;
    }


    /**
     * Destructor to cloce connection
     */
    public function __destruct()
    {
        $this->close();
    }


}