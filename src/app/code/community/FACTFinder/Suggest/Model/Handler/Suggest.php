<?php
/**
 * FACTFinder_Suggest
 *
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * Handles Suggest data
 *
 * @category Mage
 * @package FACTFinder_Suggest
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Suggest_Model_Handler_Suggest extends FACTFinder_Core_Model_Handler_Abstract
{

    protected $_query;

    protected $_jsCallback;

    protected $_suggestResult;

    protected $_suggestResultAsArray;

    protected $_primaryChannel;

    protected $_secondaryChannels;

    /**
     * Facade model to use
     *
     * @var string
     */
    protected $_facadeModel = 'factfinder_suggest/facade';


    /**
     * We might need to supply the facade manually, because we might not have a full Magento
     * context and we cannot call Mage::getSingleton().
     * @param string                          $query
     * @param string                          $jsCallback
     * @param FACTFinder_Suggest_Model_Facade $facade
     */
    public function __construct($query, $jsCallback = '', $facade = null)
    {
        $this->_facade = $facade;
        $this->_query = $query;

        // prevent xss (<script> tags can open a vulnerability)
        $this->_jsCallback = strip_tags($jsCallback);

        parent::__construct();
    }


    /**
     * Set config params to the adapter
     *
     * @return void
     */
    protected function _configureFacade()
    {
        $params = array();
        $params['format'] = 'json';
        $params['query'] = $this->_query;

        $this->_primaryChannel = $this->_getFacade()->getConfiguration()->getChannel();
        $this->_secondaryChannels = $this->_getFacade()->getConfiguration()->getSecondaryChannels();

        $this->_getFacade()->configureSuggestAdapter($params);
    }


    /**
     * Get suggestions as string
     *
     * @return string
     */
    public function getSuggestions()
    {
        if ($this->_suggestResult === null) {
            $this->_suggestResult = $this->_assembleSuggestResult();
        }

        return $this->_suggestResult;
    }


    /**
     * Retrieve suggestions array
     *
     * @return array
     */
    public function getSuggestionsAsArray()
    {
        if ($this->_suggestResultAsArray === null) {
            $this->_suggestResultAsArray = $this->_assembleSuggestResultAsArray();
        }

        return $this->_suggestResultAsArray;
    }


    /**
     * Encode suggest result to json
     *
     * @return string
     */
    protected function _assembleSuggestResult()
    {
        // Retrieve and merge all suggestions
        // Add a new "channel" field in the process

        $mergedSuggestResults = array();
        $suggestResult = $this->_getAndSanitizeSuggestions();
        if ($suggestResult != null){
            $mergedSuggestResults = $suggestResult;
        }

        foreach ($this->_secondaryChannels AS $channel) {
            $params = array('channel' => $channel);
            $this->_getFacade()->configureSuggestAdapter($params, $channel);

            $result = $this->_getAndSanitizeSuggestions($channel);
            if ($result != null){
                $mergedSuggestResults = array_merge($mergedSuggestResults, $result);
            }
        }

        $resultArray = array('suggestions' => array());
        if ($mergedSuggestResults) {
            foreach ($mergedSuggestResults as $resultQuery) {
                /** @var $resultQuery FACTFinder\Data\SuggestQuery */
                $resultArray['suggestions'][] = array(
                    'attributes'   => $resultQuery->getAttributes(),
                    'hitCount'     => $resultQuery->getHitCount(),
                    'image'        => $resultQuery->getImageUrl(),
                    'searchParams' => $resultQuery->getUrl(),
                    'type'         => $resultQuery->getType(),
                    'name'         => $resultQuery->getLabel(),
                    'channel'      => $resultQuery->channel
                );
            }
        }
        return $this->_jsCallback . '(' . Zend_Json_Encoder::encode($resultArray) . ');';
    }


    /**
     * Decode suggest result from json to array
     *
     * @return mixed
     *
     * @throws \Zend_Json_Exception
     */
    protected function _assembleSuggestResultAsArray()
    {
        // TODO: Multiple channels
        return Zend_Json_Decoder::decode($this->_getAndSanitizeSuggestions());
    }


    /**
     * Get sanitazed string of suggestions
     *
     * @param string $channel
     *
     * @return string
     */
    protected function _getAndSanitizeSuggestions($channel = null)
    {
        $result = $this->_getFacade()->getSuggestions($channel);
        if ($result === null) {
            $result = '';
        }

        if (is_array($result)) {
            foreach ($result as $item) {
                $item->channel = $channel ? $channel : $this->_primaryChannel;
            }
        }

        return $result;
    }


}
