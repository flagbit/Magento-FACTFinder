<?php
class FACTFinder_Suggest_Model_Facade extends FACTFinder_Core_Model_Facade
{
    public function configureSuggestAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "suggest", $channel, $id);
    }

    public function getSuggestions($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("suggest", "getSuggestions", $channel, $id);
    }

    public function getSuggestUrl()
    {
        return $this->_getUrlBuilder()
            ->getNonAuthenticationUrl('Suggest.ff', $this->_dic['requestParser']->getRequestParameters());
    }
}