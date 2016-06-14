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
 * This class adds autoloading support for namespaces which are used by
 * the new library.
 *
 * Class FACTFinder_Core_Model_Autoloader
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
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
     *
     * @return void
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
     * @param string $class
     *
     * @return void
     */
    public function autoload($class)
    {
        $classFile = str_replace('\\', '/', $class) . '.php';

        if (strpos($classFile, '/') !== false) {
            if (file_exists(stream_resolve_include_path($classFile))) {
                include $classFile;
            }
        }
    }


}
