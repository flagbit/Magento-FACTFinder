<?php
/**
 * Handles Suggest data
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: Suggest.php 17.09.12 08:58 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_Suggest
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    protected $_query;
    protected $_jqueryCallback;
    protected $_suggestResult;
    protected $_suggestResultAsArray;

    protected $_primaryChannel;
    protected $_secondaryChannels;

    /**
     * We might need to supply the facade manually, because we might not have a full Magento
     * context and we cannot call Mage::getSingleton().
     */
    public function __construct($query, $jqueryCallback = '', $facade = null)
    {
        $this->_facade = $facade;
        $this->_query = $query;
        $this->_jqueryCallback = $jqueryCallback;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFacade()
    {
        $params = array();
        $params['format'] = 'json';
        $params['query'] = $this->_query;

        $this->_primaryChannel = FF::getSingleton('configuration')->getChannel();
        $this->_secondaryChannels = FF::getSingleton('configuration')->getSecondaryChannels();

        $this->_getFacade()->configureSuggestAdapter($params);
        foreach($this->_secondaryChannels AS $channel)
            $this->_getFacade()->configureSuggestAdapter($params, $channel);

    }

    public function getSuggestions()
    {
        if($this->_suggestResult === null)
        {
            $this->_suggestResult = $this->_assembleSuggestResult();
        }
        return $this->_suggestResult;
    }

    public function getSuggestionsAsArray()
    {
        if($this->_suggestResultAsArray === null)
        {
            $this->_suggestResultAsArray = $this->_assembleSuggestResultAsArray();
        }
        return $this->_suggestResultAsArray;
    }

    protected function _assembleSuggestResult()
    {
        // Retrieve and merge all suggestions
        // Add a new "channel" field in the process

        $suggestResult = Zend_Json_Decoder::decode($this->_getFacade()->getSuggestions());
        foreach($suggestResult as &$item)
            $item["channel"] = $this->_primaryChannel;


        foreach($this->_secondaryChannels AS $channel)
        {
            $result = Zend_Json_Decoder::decode($this->_getFacade()->getSuggestions($channel));
            foreach($result as &$item)
                $item["channel"] = $channel;

            $suggestResult = array_merge($suggestResult, $result);
        }

        return $this->_jqueryCallback.'('.Zend_Json_Encoder::encode($suggestResult).');';
    }

    protected function _assembleSuggestResultAsArray()
    {
        // TODO: Multiple channels
        return Zend_Json_Decoder::decode($this->getSuggestions());
    }
}