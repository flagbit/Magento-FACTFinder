<?php
/**
 * Handles Search data for secondary channels
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: SecondarySearch.php 17.09.12 11:02 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_SecondarySearch
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_secondaryChannels;
    protected $_secondarySearchResults = array();

    protected function configureFacade()
    {
        $params = array();
        $params['query'] = Mage::helper('factfinder/search')->getQueryText();
        $this->_secondaryChannels = FF::getSingleton('configuration')->getSecondaryChannels();

        foreach($this->_secondaryChannels AS $channel)
        {
            $params['channel'] = $channel;
            $this->_getFacade()->configureSearchAdapter($params, $channel);
        }
    }

    public function getSecondarySearchResult($channel)
    {
        if(!in_array($channel, $this->_secondaryChannels))
        {
            Mage::logException(new Exception("Tried to query a channel that was not configured as a secondary channel."));
            return array();
        }

        if(!isset($this->_secondarySearchResults[$channel]))
        {
            $this->_secondarySearchResults[$channel] = $this->_getFacade()->getSearchResult($channel);
        }

        return $this->_secondarySearchResults[$channel];
    }
}
