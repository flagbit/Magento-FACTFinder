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
 * Helper class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Helper_Debug extends Mage_Core_Helper_Abstract
    implements FACTFinder\Util\LoggerInterface
{

    /**
     * Module Configuration File
     *
     * @var string
     */
    const MODULE_CONFIG_FILE = 'config.xml';

    /**
     * Module Log File
     *
     * @var string
     */
    const LOG_FILE_NAME = 'factfinder.log';

    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_DEBUG_MODE = 'factfinder/config/debug';

    /**
     * @var Log4PhpLogger
     */
    protected static $_loggerInstance = null;


    /**
     * Returns a new logger with the given name.
     *
     * @param string $name Name of the logger. This should be the fully
     *                     qualified name of the class using this instance,
     *                     so that different sub-namespaces can be configured
     *                     differently. Note that in the configuration file, the
     *                     loggers need to be qualified with periods instead of
     *                     backslashes.
     *
     * @return Log4PhpLogger
     */
    public static function getLogger($name)
    {
        if (self::$_loggerInstance === null) {
            self::$_loggerInstance = new self;
        }

        return self::$_loggerInstance;
    }


    /**
     * Check if the debug mode is enabled
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return (bool) Mage::getStoreConfig(self::XML_CONFIG_PATH_DEBUG_MODE);
    }


    /**
     * Debug Log to file var/log/factfinder.log
     *
     * @param mixed $message
     * @param bool $ignoreDebugConfig pass true if debug mode configuration should be ignored
     *
     * @return $this
     */
    public function log($message, $ignoreDebugConfig=false)
    {
        if (!Mage::getConfig()) {
            return $this;
        }

        try {
            if ($this->isDebugMode() || $ignoreDebugConfig) {
                Mage::log($message, null, self::LOG_FILE_NAME, true);
            }
        } catch (Exception $e){
            // actually do nothing
            return $this;
        }

        return $this;
    }


    /**
     * Log message with prefix TRACE
     *
     * @param mixed $message
     *
     * @return \FACTFinder_Core_Helper_Debug
     */
    public function trace($message)
    {
        return $this->log('TRACE: ' . $message);
    }


    /**
     * Log message with prefix TRACE
     *
     * @param mixed $message
     *
     * @return \FACTFinder_Core_Helper_Debug
     */
    public function debug($message)
    {
        return $this->log('DEBUG: ' . $message);
    }


    /**
     * Log message with prefix INFO
     *
     * @param mixed $message
     *
     * @return \FACTFinder_Core_Helper_Debug
     */
    public function info($message)
    {
        return $this->log('INFO: ' . $message);
    }


    /**
     * Log message with prefix WARNING
     *
     * @param mixed $message
     *
     * @return \FACTFinder_Core_Helper_Debug
     */
    public function warn($message)
    {
        return $this->log('WARNING: ' . $message, true);
    }


    /**
     * Log message with prefix ERROR
     *
     * @param mixed $message
     *
     * @return \FACTFinder_Core_Helper_Debug
     */
    public function error($message)
    {
        return $this->log('ERROR: ' . $message, true);
    }


    /**
     * Log message with prefix FATAL ERROR
     *
     * @param mixed $message
     *
     * @return \FACTFinder_Core_Helper_Debug
     */
    public function fatal($message)
    {
        return $this->log('FATAL ERROR: ' . $message, true);
    }


}
