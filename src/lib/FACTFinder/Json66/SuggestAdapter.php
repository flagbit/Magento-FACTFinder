<?php
/**
 * suggest adapter using the json interface. expects a json formated string from the dataprovider
 */
class FACTFinder_Json66_SuggestAdapter extends FACTFinder_Http_SuggestAdapter
{
    /**
     * init
     */
    protected function init()
    {
        parent::init();
        $this->getDataProvider()->setParam('format', 'json');
    }

    /**
     * try to parse data as json
     *
     * @throws Exception of data is no valid JSON
     * @return stdClass
     */
    protected function getData()
    {
        $jsonData = json_decode(parent::getData());
        if ($jsonData === null)
            throw new InvalidArgumentException("json_decode() raised error ".json_last_error());
        return $jsonData;
    }

    /**
     * @return array of FACTFinder_SuggestQuery objects
     */
    protected function createSuggestions()
    {
        $jsonData = $this->getData();
        
        $suggestions = array();
        foreach($jsonData as $suggestData)
            $suggestions[] = $this->createSuggestQueryFrom($suggestData);

        return $suggestions;
    }
    
    /**
     * @return FACTFinder_SuggestQuery
     **/
    protected function createSuggestQueryFrom($suggestData)
    {
        $query = strval($suggestData->query);
        return FF::getInstance('suggestQuery',
            $this->getEncodingHandler()->encodeServerContentForPage($query),
            $this->getParamsParser()->createPageLink(array('query' => $query)),
            strval($suggestData->hitCount),
            $this->getEncodingHandler()->encodeServerContentForPage(strval($suggestData->type)),
            strval($suggestData->imageURL)
        );
    }
}