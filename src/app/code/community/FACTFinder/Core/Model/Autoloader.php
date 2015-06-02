<?php
/**
 * This class adds autoloading support for namespaces which are used by
 * the new library.
 *
 * Class Flagbit_FactFinder_Model_Autoloader
 */
class FACTFinder_Core_Model_Autoloader
{

    /**
     * @var bool
     */
    protected static $registered = false;


    /**
     * Add autoloader class
     *
     * @param \Varien_Event_Observer $observer
     */
    public function addAutoloader(Varien_Event_Observer $observer)
    {
        if (self::$registered) {
            return;
        }
        spl_autoload_register(array($this, 'autoload'), false, true);
        self::$registered = true;
    }


    /**
     * Autoload method
     *
     * @param $class
     */
    public function autoload($class)
    {
        $classFile = str_replace('\\', '/', $class) . '.php';

        if (strpos($classFile, '/') !== false) {
            include $classFile;
        }
    }

}