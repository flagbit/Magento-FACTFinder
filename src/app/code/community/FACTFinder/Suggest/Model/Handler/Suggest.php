<?php

/**
 * Handles Suggest data
 *
 */
class FACTFinder_Suggest_Model_Handler_Suggest extends FACTFinder_Core_Model_Handler_Abstract
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
    protected function _configureFacade()
    {
        $params = array();
        $params['format'] = 'json';
        $params['query'] = $this->_query;

        $this->_primaryChannel = $this->_getFacade()->getConfiguration()->getChannel();
        $this->_secondaryChannels = $this->_getFacade()->getConfiguration()->getSecondaryChannels();

        $this->_getFacade()->configureSuggestAdapter($params);
        foreach ($this->_secondaryChannels AS $channel) {
            $this->_getFacade()->configureSuggestAdapter($params, $channel);
        }
    }

    public function getSuggestions()
    {
        if ($this->_suggestResult === null) {
            $this->_suggestResult = $this->_assembleSuggestResult();
        }

        return $this->_suggestResult;
    }

    public function getSuggestionsAsArray()
    {
        if ($this->_suggestResultAsArray === null) {
            $this->_suggestResultAsArray = $this->_assembleSuggestResultAsArray();
        }

        return $this->_suggestResultAsArray;
    }

    protected function _assembleSuggestResult()
    {
        // Retrieve and merge all suggestions
        // Add a new "channel" field in the process

        $suggestResult = $this->_getAndSanitizeSuggestions();

        foreach ($this->_secondaryChannels AS $channel) {
            $result = $this->_getAndSanitizeSuggestions($channel);
            $suggestResult = array_merge($suggestResult, $result);
        }

        $resultArray = array();
        foreach ($suggestResult as $resultQuery) {
            /* @var $resultQuery FACTFinder\Data\SuggestQuery */
            $resultArray['suggestions'][] = array(
                'attributes'   => $resultQuery->getAttributes(),
                'hitCount'     => $resultQuery->getHitCount(),
                'image'        => $resultQuery->getImageUrl(),
                'searchParams' => $resultQuery->getUrl(),
                'type'         => $resultQuery->getType(),
                'label'         => $resultQuery->getLabel(),
            );
        }

        return $this->_jqueryCallback . '(' . Zend_Json_Encoder::encode($resultArray) . ');';
    }

    protected function _assembleSuggestResultAsArray()
    {
        // TODO: Multiple channels
        return Zend_Json_Decoder::decode($this->_getAndSanitizeSuggestions());
    }

    protected function _getAndSanitizeSuggestions($channel = null)
    {
        $result = $this->_getFacade()->getSuggestions($channel);
        if ($result === null) {
            $result = '';
        }

        return $result;
    }
}