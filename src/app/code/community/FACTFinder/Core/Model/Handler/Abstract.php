<?php
abstract class FACTFinder_Core_Model_Handler_Abstract
{
    /**
     * @var FACTFinder_Core_Model_Facade
     **/
    protected $_facade;


    /**
     *
     */
    public function __construct()
    {
        $this->_configureFacade();
    }


    /**
     * Implement this method to set up any adapters the handler will need later on.
     * WARNING: You will always have to configure...() the adapter once, even if you do not set any parameters.
     *          This will register the adapter with the Facade and lets it query all data in parallel.
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
            $this->_facade = Mage::getSingleton('factfinder/facade');
        }

        return $this->_facade;
    }
}