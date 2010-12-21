<?php

/**
 * handles different loading tasks
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Loader.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 */
 
// init
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('LIB_DIR')) {
	define('LIB_DIR', dirname(dirname(__FILE__)));
}

set_include_path(LIB_DIR . PATH_SEPARATOR . get_include_path());
spl_autoload_register(array('FACTFinder_Loader', 'autoload'));

// don't know, whether I should do that
if (function_exists('__autoload') && array_search('__autoload', spl_autoload_functions()) === false) {
    spl_autoload_register('__autoload');
}

final class FF extends FACTFinder_Loader{} //shortcut for the loader class
class FACTFinder_Loader
{
	private static $singletons = array();
	private static $classNames = array();
	
	public static function autoload($classname)
	{
		$filename = self::getFilename($classname);
		if (file_exists($filename)) {
			include_once $filename;
		}
	}
	
	private static function getFilename($classname)
	{
		return LIB_DIR . DS . str_replace('_', DS, $classname) . '.php';
	}
	
	private static function canLoadClass($classname)
	{
		return file_exists(self::getFilename($classname));
	}
	
	/**
	 * Creates an instance of a class taking into account classes with the prefix "Custom_" instead of "FACTFinder_".
	 * USE THIS method instead of the PHP "new" keyword.
	 * Eg. "$obj = new myclass;" should be "$obj = FACTFinder_Loader::getInstance("myclass")" instead!
	 * You can also pass arguments for a constructor:
	 * 	FACTFinder_Loader::getInstance('myClass', $arg1, $arg2,  ..., $argN)
	 *
	 * @param	string class name to instantiate
	 * @param	mixed optional as many parameters as the class needs to be created
	 * @return	object A reference to the object
	 */
	public static function getInstance($name)
	{
		if (isset(self::$classNames[$name])) {
			$className = self::$classNames[$name];
		} else {
			$className = self::getClassName($name);
			self::$classNames[$name] = $className;
		}
		
		// this snippet is from the typo3 class "t3lib_div" writen by Kasper Skaarhoj <kasperYYYY@typo3.com>
		if (func_num_args() > 1) {
			// getting the constructor arguments by removing this
			// method's first argument (the class name)
			$constructorArguments = func_get_args();
			array_shift($constructorArguments);

			$reflectedClass = new ReflectionClass($className);
			$instance = $reflectedClass->newInstanceArgs($constructorArguments);
		} else {
			$instance = new $className;
		}
		
		return $instance;
	}
	
	/**
	 * creates an instance of the class once and returns it everytime. uses getInstance
	 *
	 * @param	string class name to instantiate
	 * @param	mixed optional as many parameters as the class needs to be created
	 * @return	object A reference to the object
	 */
	public static function getSingleton($name)
	{
		if (!isset(self::$singletons[$name])) {
			$params = func_get_args();
			self::$singletons[$name] = call_user_func_array(array("self", "getInstance"), $params);
		}
		return self::$singletons[$name];
	}
	
	/**
	 * check whether there is a custom class with the prefix "Custom_" instead of "FACTFinder_"
	 * if non of them exists, it also checks if the name is the classname itselft
	 */
	private static function getClassName($name)
	{
		$name = trim(str_replace('factfinder/', '', $name));
		$name = str_replace(' ', '_', ucwords(str_replace('/', ' ', $name)));
		
		// check whether there is a custom or lib-unrelated class
		$oldCustomClassName  = 'Custom_' . $name;
		$customClassName     = 'FACTFinderCustom_' . $name;
		$factfinderClassName = 'FACTFinder_' . $name;
		$defaultClassName    = $name;
		
		if (self::canLoadClass($customClassName)) {
			$className = $customClassName;
		} else if (self::canLoadClass($oldCustomClassName)) {
			$className = $oldCustomClassName;
		} else if (self::canLoadClass($factfinderClassName)) {
			$className = $factfinderClassName;
		} else if (class_exists($defaultClassName)) { //trigger other autload methods
			$className = $defaultClassName;
		} else {
			throw new Exception("class '$defaultClassName' not found");
		}
		return $className;
	}
}