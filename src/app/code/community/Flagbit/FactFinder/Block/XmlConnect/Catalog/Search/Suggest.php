<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Block class
 * 
 * This class enables FAC-Finder sugguest
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
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
              
        $suggestData = Mage::getSingleton('factfinder/adapter')->getSuggestResult($this->getRequest()->getParam('q'));
        
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