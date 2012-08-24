<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this class implements the FACTFinder logger interface by doing nothing (this will be used if no logger is created or no logging is wanted).
 *
 * @package FACTFinder\Common
 */
 
class FACTFinder_NullLogger implements FACTFinder_Abstract_Logger
{
	public function trace($message)	{}	
	public function debug($message)	{}	
	public function info($message)	{}
	public function warn($message)	{}
	public function error($message) {}
	public function fatal($message)	{}	
}