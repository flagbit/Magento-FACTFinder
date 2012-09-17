<?php
/**
 * Checks whether the configuration is working
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: CheckStatus.php 17.09.12 15:00 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_CheckStatus
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_configArray;

    public function __construct($configArray = null)
    {
        $this->_configArray = $configArray;
        parent::__construct();
    }

    protected function configureFacade()
    {
        FF::getSingleton('configuration', $this->_configArray);

        $params = array();

        $params['query'] = 'FACT-Finder Version';
        $params['productsPerPage'] = '1';

        $this->_getFacade()->configureSearchAdapter($params);
    }

    public function checkStatus()
    {
        return $this->_getFacade()->getSearchStatus() == 'resultsFound';
    }
}
