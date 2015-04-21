<?php

/**
 * Block class
 *
 * This class enables FAC-Finder suggest
 *
 */
class Flagbit_FactFinder_Block_XmlConnect_Catalog_Search_Suggest extends Mage_XmlConnect_Block_Catalog_Search_Suggest
{
    const SUGGEST_ITEM_SEPARATOR = '::sep::';

    /**
     * Search suggestions xml renderer
     *
     * @return string
     */
    protected function _toHtml()
    {
        $suggestXmlObj = new Mage_XmlConnect_Model_Simplexml_Element('<suggestions></suggestions>');
        if (!$this->getRequest()->getParam('q', false)) {
            return $suggestXmlObj->asNiceXml();
        }

        $handler = Mage::getSingleton('factfinder_suggest/handler_suggest', array($this->getRequest()->getParam('q')));

        $suggestData = $handler->getSuggestionsAsArray();

        if (!($count = count($suggestData))) {
            return $suggestXmlObj->asNiceXml();
        }

        $items = '';
        foreach ($suggestData as $index => $item) {
            $items .= $suggestXmlObj->xmlentities(strip_tags($item['query']))
                . self::SUGGEST_ITEM_SEPARATOR
                . (int)$item['hitCount']
                . self::SUGGEST_ITEM_SEPARATOR;
        }

        $suggestXmlObj = new Mage_XmlConnect_Model_Simplexml_Element('<suggestions>' . $items . '</suggestions>');

        return $suggestXmlObj->asNiceXml();
    }

}