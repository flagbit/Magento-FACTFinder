<?php
class Flagbit_FactFinder_Block_TagCloud extends Mage_CatalogSearch_Block_Term
{
    protected function _loadTerms()
    {
        if (!Mage::helper('factfinder/search')->getIsEnabled(false, 'tagcloud')) {
            return parent::_loadTerms();
        }
        
        if (empty($this->_terms)) {
            $terms = Mage::getSingleton('factfinder/adapter')->getTagCloud();
            
            if (count($terms) == 0) {
                return $this;
            }
            
            $this->_maxPopularity = 0;
            $this->_minPopularity = 1;
            
            for ($i = 0; $i < count($terms); $i++) {
                $term = $terms[$i];
                
                $termArray = array();
                $termArray['name'] = $term->getValue();
                $termArray['url'] = $term->getUrl();
                $termArray['popularity'] = $term->getWeight();
                $termArray['ratio'] = $term->getWeight();
                
                if ($term->getWeight() > $this->_maxPopularity) {
                    $this->_maxPopularity = $term->getWeight();
                }
                if ($term->getWeight() < $this->_minPopularity) {
                    $this->_minPopularity = $term->getWeight();
                }
                
                $terms[$i] = new Varien_Object($termArray);
            }
            
            $this->_terms = $terms;
        }
        
        return $this;
    }
}