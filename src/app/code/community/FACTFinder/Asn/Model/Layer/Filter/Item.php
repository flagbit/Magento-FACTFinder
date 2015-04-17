<?php

class FACTFinder_Asn_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        $params['_use_rewrite'] = true;
        $params['_query'] = $this->getQueryParams();
        $params['_escape'] = true;

        if ($this->getSeoPath() && $this->_isOnSearchPage()) {
            $query['q'] = null;
            $params['_direct'] = 's' . $this->getSeoPath();
        }

        return Mage::getUrl('*/*/*', $params);
    }


    /**
     * Get url for remove whole filter
     *
     * @return string
     */
    public function getRemoveFilterUrl()
    {
        $params['_use_rewrite'] = true;
        $params['_query'] = $this->getQueryParams();
        $params['_escape'] = true;

        unset($params['_query'][$this->getRequestVar()]);

        if ($this->getSeoPath() && $this->_isOnSearchPage()) {
            $query['q'] = null;
            $params['_direct'] = 's' . $this->getSeoPath();
        }

        return Mage::getUrl('*/*/*', $params);
    }


    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        $query = array(
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        $query = array_merge(
            $query,
            $this->getQueryParams()
        );

        if ($this->getSeoPath() && $this->_isOnSearchPage()) {
            if ($query['q'] != '*') {
                $query['q'] = null;
            }

            return Mage::getUrl('*/*/*', array('_query' => $query, '_direct' => 's' . $this->getSeoPath()));
        }

        return Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true, '_query' => $query));
    }


    /**
     * Check if we're on search page
     *
     * @return bool
     */
    protected function _isOnSearchPage()
    {
        return Mage::helper('factfinder_asn')->getIsOnSearchPage();
    }

}