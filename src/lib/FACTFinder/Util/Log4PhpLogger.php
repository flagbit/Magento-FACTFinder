<?php
namespace FACTFinder\Util;

/**
 * Implements LoggerInterface by using log4php's Logger class.
 */
include_once FF_LIB_DIR . DS . 'log4php' . DS . 'Logger.php';

class Log4PhpLogger implements LoggerInterface
{
	protected $log;

    /**
     * Configures the static Logger class supplied by log4php.
     *
     * @param    string $fileName Name of the configuration file
     */
	public static function configure($fileName)
	{
		\Logger::configure($fileName);
	}

	/**
	 * Returns a new logger with the given name.
	 * @param string $name Name of the logger. This should be the fully
	 *                     qualified name of the class using this instance,
	 *                     so that different sub-namespaces can be configured
	 *                     differently. Note that in the configuration file, the
	 * 					   loggers need to be qualified with periods instead of
	 *                     backslashes.
	 * @return Log4PhpLogger
	 */
	public static function getLogger($name)
	{
		$name = str_replace('\\', '.', $name);
		return new Log4PhpLogger($name);
	}

	protected function __construct($name)
	{
		$this->log = \Logger::getLogger($name);
	}

	public function trace($message)
	{
		$this->log->trace($message);
	}

	public function debug($message)
	{
		$this->log->debug($message);
	}

	public function info($message)
	{
		$this->log->info($message);
	}

	public function warn($message)
	{
		$this->log->warn($message);
	}

	public function error($message)
	{
		$this->log->error($message);
	}

	public function fatal($message)
	{
		$this->log->fatal($message);
	}

}
