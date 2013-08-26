<?php
/**
 * suggest adapter using the json interface. expects a json formated string from the dataprovider
 */
class FACTFinder_Json68_SuggestAdapter extends FACTFinder_Json67_SuggestAdapter
{
    /**
     * TODO: Utilize all new Suggest features
     **/

    protected function createSuggestQueryFrom($suggestData)
    {
        $query = strval($suggestData->name);
        return FF::getInstance('suggestQuery',
            $this->encodingHandler->encodeServerContentForPage($query),
            $this->paramsParser->createPageLink($this->paramsParser->parseParamsFromString(strval($suggestData->searchParams))),
            strval($suggestData->hitCount),
            $this->encodingHandler->encodeServerContentForPage(strval($suggestData->type)),
            strval($suggestData->image)
        );
    }
}