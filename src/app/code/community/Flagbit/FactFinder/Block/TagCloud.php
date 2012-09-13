<?php
class Flagbit_FactFinder_Block_TagCloud extends Mage_CatalogSearch_Block_Term
{
    /**
     * @var Flagbit_FactFinder_Model_Handler_TagCloud
     */
    protected $_tagCloudHandler;

    protected function _prepareLayout()
    {
        $this->_tagCloudHandler = Mage::getSingleton('factfinder/handler_tagCloud');
        return parent::_prepareLayout();
    }

    /**
     * @return Flagbit_FactFinder_Block_TagCloud|Mage_CatalogSearch_Block_Term
     */
    protected function _loadTerms()
    {
        if (!Mage::helper('factfinder/search')->getIsEnabled(false, 'tagcloud')) {
            return parent::_loadTerms();
        }
        
        if (empty($this->_terms)) {
            $this->_terms = $this->_tagCloudHandler->getTerms();
            
            if (count($this->_terms) == 0)
                return $this;

            $this->determineMinMaxPopularity();
        }
        
        return $this;
    }

    /**
     * Determines minimum and maximum popularity among terms
     */
    protected function determineMinMaxPopularity()
    {
        $this->_maxPopularity = 0;
        $this->_minPopularity = 1;

        foreach ($this->_terms as $term) {
            if ($term->getPopularity() > $this->_maxPopularity)
                $this->_maxPopularity = $term->getPopularity();

            if ($term->getPopularity() < $this->_minPopularity)
                $this->_minPopularity = $term->getPopularity();
        }
    }
}