<?php
/**
 * suggest adapter using the json interface. expects a json formated string from the dataprovider
 */
class FACTFinder_Json69_SuggestAdapter extends FACTFinder_Json68_SuggestAdapter
{
    /**
     * TODO: Utilize all new Suggest features
     **/

    protected function createSuggestQueryFrom($suggestData)
    {
        $suggestQuery = parent::createSuggestQueryFrom($suggestData);
        $suggestQuery->setRefKey(strval($suggestData->refKey));
        return $suggestQuery;
    }
}