<?php
/**
 * Abstract class for Fact Finder object handlers
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: Abstract.php 13.09.12 14:19 $
 *
 **/
abstract class Flagbit_FactFinder_Model_Handler_Abstract
{
    /**
     * @var Flagbit_FactFinder_Model_Facade
     **/
    protected $_facade;

    /**
     *
     */
    public function __construct()
    {
        $this->configureFacade();
    }

    /**
     * Implement this method to set up any adapters the handler will need later on.
     * WARNING: You will always have to configure...() the adapter once, even if you do not set any parameters.
     *          This will register the adapter with the Facade and lets it query all data in parallel.
     */
    abstract protected function configureFacade();

    /**
     * @return Flagbit_FactFinder_Model_Facade
     */
    protected function _getFacade()
    {
        if($this->_facade === null)
        {
            $this->_facade = Mage::getSingleton('factfinder/facade');
        }
        return $this->_facade;
    }
}
