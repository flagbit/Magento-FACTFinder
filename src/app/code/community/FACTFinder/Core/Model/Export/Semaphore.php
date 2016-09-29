<?php
/**
 * Semaphore.php
 *
 * @category Mage
 * @package FACTFinder
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link http://www.flagbit.de
 */

/**
 * Semaphore class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Export_Semaphore
{
    const LOCK_PREFIX = 'ffexport_';

    /**
     * @var string
     */
    protected $_locksDir;

    /**
     * @var int
     */
    protected $_storeId = 0;

    /**
     * @var string
     */
    protected $_type = '';


    /**
     * FACTFinder_Core_Model_Export_Semaphore constructor.
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        if (isset($params['store_id'])) {
            $this->setStoreId($params['store_id']);
        }

        if (isset($params['type'])) {
            $this->setType($params['type']);
        }

        $this->_locksDir = Mage::getBaseDir('var') . DS . 'locks';
        Mage::getConfig()->createDirIfNotExists($this->_locksDir);
    }


    /**
     * Set store id
     *
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
     * Set export type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;

        return $this;
    }


    /**
     * Create lock by making a lock file
     *
     * Throws an exception if it was already locked and the timeout has not run out yet
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function lock()
    {
        $mtime = @filemtime($this->getLockFileName());
        $semaphoreTimeout = FACTFinderCustom_Configuration::DEFAULT_SEMAPHORE_TIMEOUT;
        if ($mtime && time() - $mtime < $semaphoreTimeout) {
            throw new RuntimeException();
        }
        @touch($this->getLockFileName());
    }


    /**
     * Retrieve the name of lock file
     *
     * @return string
     */
    public function getLockFileName()
    {
        return $this->_locksDir . DS . self::LOCK_PREFIX . $this->_type . $this->_storeId . '.lock';
    }


    /**
     * Remove the lock file
     *
     * @return void
     */
    public function release()
    {
        @unlink($this->getLockFileName());
    }


}
