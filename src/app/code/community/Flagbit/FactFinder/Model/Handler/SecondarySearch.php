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

    /**
     * returns the FACTFinder_Result object of the given channel.
     *
     * @param $channel
     * @return FACTFinder_Result
     */
    public function getSecondarySearchResult($channel)
    {
        if(!$this->isSecondaryChannel($channel))
        {
            return array();
        }

        if(!isset($this->_secondarySearchResults[$channel]))
        {
            $this->_secondarySearchResults[$channel] = $this->_getFacade()->getSearchResult($channel);
        }

        return $this->_secondarySearchResults[$channel];
    }

    /**
     * returns the search adapter of the FACT-Finder library the module is based on. This adapter gives access to all
     * search result relevant objects of the channel's result.
     *
     * if there is no adapter for that channel, an error is logged an null is returned.
     *
     * @param $channel
     * @return FACTFinder_Default_SearchAdapter (or one of the version specific implementations)
     */
    public function getSecondarySearchAdapter($channel)
    {
        echo "$channel <br>";
        print_r($this->isSecondaryChannel($channel));
        if(!$this->isSecondaryChannel($channel))
        {
            return null;
        }

        if(!isset($this->_secondarySearchAdapters[$channel]))
        {
            $this->_secondarySearchAdapters[$channel] = $this->_getFacade()->getSearchAdapter($channel);
        }

        return $this->_secondarySearchAdapters[$channel];
    }

    /**
     * returns true if the set channel is a secondary channel. if it is not, it also logs an error
     *
     * @param $channel
     * @return bool
     */
    private function isSecondaryChannel($channel)
    {
        if(!in_array($channel, $this->_secondaryChannels))
        {
            Mage::logException(new Exception("Tried to query a channel that was not configured as a secondary channel."));
            return false;
        } else {
            return true;
        }
    }
}
