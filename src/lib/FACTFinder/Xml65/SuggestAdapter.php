<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Xml65
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * suggest adapter using the xml interface. expects a xml formated string from the dataprovider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SuggestAdapter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Xml65_SuggestAdapter extends FACTFinder_Http_SuggestAdapter
{
    /**
     * init
     */
    protected function init()
    {
        parent::init();
        $this->getDataProvider()->setParam('format', 'xml');
    }

    /**
     * try to parse data as xml
     *
     * @throws Exception of data is no valid XML
     * @return SimpleXMLElement
     */
    protected function getData()
    {
        libxml_use_internal_errors(true);
        return new SimpleXMLElement(parent::getData()); //throws exception on error
    }

    /**
     * this implementation returns raw suggestions strings
     *
     * @return array of FACTFinder_SuggestQuery objects
     */
    protected function createSuggestions()
    {
        $xmlResult = $this->getData();
        $encodingHandler = $this->getEncodingHandler();
        $paramsParser = $this->getParamsParser();
        $suggest = array();
        if (!empty($xmlResult)) {
            foreach($xmlResult->suggest AS $xmlSuggestQuery) {
                $query = strval($xmlSuggestQuery->attributes()->query);
                $suggest[] = FF::getInstance('suggestQuery',
                    $encodingHandler->encodeServerContentForPage($query),
                    $paramsParser->createPageLink(array('query' => $query)),
                    strval($xmlSuggestQuery->attributes()->hitcount),
                    $encodingHandler->encodeServerContentForPage(strval($xmlSuggestQuery->attributes()->type)),
					isset($xmlSuggestQuery->attributes()->hitcount) ? strval($xmlSuggestQuery->attributes()->hitcount) : ''
                );
            }
        }
        return $suggest;
    }
}