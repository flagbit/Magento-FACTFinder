<?php
/**
 * FACTFinder_TagCloud
 *
 * @category Mage
 * @package FACTFinder_TagCloud
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Block class
 *
 * Is used instead of the default block
 *
 * @category Mage
 * @package FACTFinder_TagCloud
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tagcloud_Block_TagCloud extends Mage_CatalogSearch_Block_Term
{


    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('factfinder')->isEnabled('tagcloud')) {
            return '';
        }

        // only show on "no result" page
        $searchResultBlock = $this->getLayout()->getBlock('search.result');
        if(!$searchResultBlock || ($searchResultBlock && $searchResultBlock->getResultCount())) {
            return '';
        }

        return parent::_toHtml();
    }


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
     *
     * @return void
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