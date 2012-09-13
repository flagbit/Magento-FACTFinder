<?php
/**
 * Handles TagCloud data
 *
 * @category    Mage
 * @package     Flagbit_FactFinder
 * @copyright   Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author      Martin Buettner <martin.buettner@omikron.net>
 * @version     $Id: TagCloud.php 13.09.12 14:10 $
 *
 **/
class Flagbit_FactFinder_Model_Handler_TagCloud
    extends Flagbit_FactFinder_Model_Handler_Abstract
{
    /**
     * @var array
     */
    protected $_tagCloud;

    /**
     * @return array
     */
    public function getTerms()
    {
        $tagCloud = $this->_getTagCloud();

        $terms = array();
        foreach ($tagCloud as $item) {
            $terms[] = new Varien_Object($this->assembleTermArray($item));
        }

        return $terms;
    }

    /**
     * @param   FACTFinder_TagQuery $item
     * @return  array
     */
    protected function assembleTermArray($item)
    {
        $termArray = array();
        $termArray['name']          = $item->getValue();
        $termArray['url']           = $item->getUrl();
        $termArray['popularity']    = $item->getWeight();
        $termArray['ratio']         = $item->getWeight();
        return $termArray;
    }

    /**
     * @return array of FACTFinder_TagQuery
     */
    protected function _getTagCloud()
    {
        if($this->_tagCloud === null)
        {
            $this->_tagCloud = $this->_getFacade()->getTagCloud();
            if ($this->_tagCloud === null)
                $this->_tagCloud = array();
        }
        return $this->_tagCloud;
    }
}
