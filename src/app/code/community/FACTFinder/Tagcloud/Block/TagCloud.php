<?php
class FACTFinder_Tagcloud_Block_TagCloud extends Mage_CatalogSearch_Block_Term
{
    /**
     * @return FACTFinder_Tagcloud_Block_TagCloud
     */
    protected function _loadTerms()
    {
        $handler = Mage::getModel('factfinder_tagcloud/handler_tagCloud');
        if (empty($this->_terms)) {
            $this->_terms = $handler->getTerms();

            if (count($this->_terms) == 0) {
                return $this;
            }

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
            if ($term->getPopularity() > $this->_maxPopularity) {
                $this->_maxPopularity = $term->getPopularity();
            }

            if ($term->getPopularity() < $this->_minPopularity) {
                $this->_minPopularity = $term->getPopularity();
            }
        }
    }
}