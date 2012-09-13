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
class Flagbit_FactFinder_Model_Handler_Abstract
{
    /**
     * @var Flagbit_FactFinder_Model_Facade
     **/
    private $_facade;

    protected function _getFacade()
    {
        if($this->_facade === null)
        {
            $this->_facade = Mage::getSingleton('factfinder/facade');
        }
        return $this->_facade;
    }
}
