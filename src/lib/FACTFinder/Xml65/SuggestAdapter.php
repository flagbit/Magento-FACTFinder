<?php

/**
 * suggest adapter using the xml interface. expects a xml formated string from the dataprovider
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Xml65
 */
class FACTFinder_Xml65_SuggestAdapter extends FACTFinder_Http_SuggestAdapter
{	
	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
		if (!empty($xmlResult->suggest)) {
			foreach($xmlResult->suggest AS $xmlSuggestQuery) {
				$query = strval($xmlSuggestQuery->attributes()->query);
				$suggest[] = FF::getInstance('suggestQuery',
					$encodingHandler->encodeServerContentForPage($query),
					$paramsParser->createPageLink(array('query' => $query)),
					strval($xmlSuggestQuery->attributes()->hitcount),
					$encodingHandler->encodeServerContentForPage(strval($xmlSuggestQuery->attributes()->type))
				);
			}
		}
		return $suggest;
	}
}