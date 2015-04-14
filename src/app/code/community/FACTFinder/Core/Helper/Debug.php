<?php
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

    protected static $_loggerInstance = null;

    /**
     * Returns a new logger with the given name.
     * @param string $name Name of the logger. This should be the fully
     *                     qualified name of the class using this instance,
     *                     so that different sub-namespaces can be configured
     *                     differently. Note that in the configuration file, the
     *                     loggers need to be qualified with periods instead of
     *                     backslashes.
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
     * Debug Log to file var/log/factfinder.log
     *
     * @param $message
     *
     * @return $this
     */
    public function log($message)
    {
        if (!Mage::getConfig()) {
            return $this;
        }

        try {
            if (Mage::getStoreConfig(self::XML_CONFIG_PATH_DEBUG_MODE)) {
                Mage::log($message, null, self::LOG_FILE_NAME, true);
            }
        } catch (Exception $e){
            // do nothing ?!!
        }

        return $this;
    }

    public function trace($message)
    {
        return $this->log('TRACE: ' . $message);
    }

    public function debug($message)
    {
        return $this->log('DEBUG: ' . $message);
    }

    public function info($message)
    {
        return $this->log('INFO: ' . $message);
    }

    public function warn($message)
    {
        return $this->log('WARNING: ' . $message);
    }

    public function error($message)
    {
        return $this->log('ERROR: ' . $message);
    }

    public function fatal($message)
    {
        return $this->log('FATAL ERROR: ' . $message);
    }

}
