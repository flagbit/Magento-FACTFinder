<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this class implements the FACTFinder logger interface and uses log4php's Logger class.
 *
 * @package FACTFinder\Common
 */

 
include_once LIB_DIR . DS . 'log4php' . DS . 'Logger.php';
 
class FACTFinder_Log4PhpLogger implements FACTFinder_Abstract_Logger
{
	protected static $lastLogger = 0;
	
	protected $name;
	
	protected $log;
	
	public function __construct()
	{
		self::$lastLogger++;
		$this->name = "logger".self::$lastLogger;
	}
	
    /**
     * configures the static Logger class supplied by log4php
	 * be aware that only the root loggers configuration will affect how the framework's interna are logged
     *
     * @param    string file name of the configuration file
     */
	public function configure($fileName)
	{
		$this->getLogger()->configure($fileName);
	}
	
	/**
     * lazily gets a logger.
     *
     * @return    Logger 		the logger
     */
	protected function getLogger()
	{
		if(!isset($this->log))
			$this->log = Logger::getLogger($this->name);
		return $this->log;
	}
	
	public function trace($message)
	{
		$this->getLogger()->trace($message);
	}
	
	public function debug($message)
	{
		$this->getLogger()->debug($message);
	}
	
	public function info($message)
	{
		$this->getLogger()->info($message);
	}
	
	public function warn($message)
	{
		$this->getLogger()->warn($message);
	}
	
	public function error($message)
	{
		$this->getLogger()->error($message);
	}
	
	public function fatal($message)
	{
		$this->getLogger()->fatal($message);
	}
	
}