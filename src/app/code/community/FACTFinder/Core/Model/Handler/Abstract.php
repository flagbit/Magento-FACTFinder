<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Abstract handler class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
abstract class FACTFinder_Core_Model_Handler_Abstract
{

    /**
     * Facade model name
     *
     * @var string
     */
    protected $_facadeModel = 'factfinder/facade';

    /**
     * @var FACTFinder_Core_Model_Facade
     **/
    protected $_facade;


    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_configureFacade();
    }


    /**
     * Implement this method to set up any adapters the handler will need later on.
     * WARNING: You will always have to configure...() the adapter once, even if you do not set any parameters.
     *          This will register the adapter with the Facade and lets it query all data in parallel.
     *
     * @return void
     */
    abstract protected function _configureFacade();


    /**
     * Get an instance of FACT-Finder facade
     *
     * @return FACTFinder_Core_Model_Facade
     */
    protected function _getFacade()
    {
        if ($this->_facade === null) {
            $this->_facade = Mage::getSingleton($this->_facadeModel);
        }

        return $this->_facade;
    }


}
