<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Abstract
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * interface to access a logger if needed
 *
 * @package FACTFinder\Abstract
 */
interface FACTFinder_Abstract_Logger
{
	public function trace($message);
	public function debug($message);
	public function info($message);
	public function warn($message);
	public function error($message);
	public function fatal($message);
}