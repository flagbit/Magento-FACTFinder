<?php

class FACTFinder_Tagcloud_Model_Handler_TagCloud extends FACTFinder_Core_Model_Handler_Abstract
{
    /**
     * @var array
     */
    protected $_tagCloud;


    /**
     * Get an instance of FACT-Finder facade
     *
     * @return FACTFinder_Tagcloud_Model_Facade
     */
    protected function _getFacade()
    {
        if ($this->_facade === null) {
            $this->_facade = Mage::getSingleton('factfinder_tagcloug/facade');
        }

        return $this->_facade;
    }


    /**
     * {@inheritdoc}
     */
    protected function _configureFacade()
    {
        // Registering the needed tag cloud adapter
        $this->_getFacade()->configureTagCloudAdapter(array());
    }

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
     *
     * @return  array
     */
    protected function assembleTermArray($item)
    {
        $termArray = array();
        $termArray['name'] = $item->getValue();
        $termArray['url'] = $item->getUrl();
        $termArray['popularity'] = $item->getWeight();
        $termArray['ratio'] = $item->getWeight();

        return $termArray;
    }

    /**
     * @return array of FACTFinder_TagQuery
     */
    protected function _getTagCloud()
    {
        if ($this->_tagCloud === null) {
            $this->_tagCloud = $this->_getFacade()->getTagCloud();
            if ($this->_tagCloud === null)
                $this->_tagCloud = array();
        }

        return $this->_tagCloud;
    }
}
