<?php 
class Flagbit_FactFinder_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        $params['_use_rewrite'] = true;
        $params['_query']       = $this->getQueryParams();
        $params['_escape']      = true;

        if($this->getSeoPath())
        {
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

        if($this->getSeoPath())
        {
            $query['q'] = null;
            return Mage::getUrl('*/*/*', array('_query'=>$query, '_direct' => 's' . $this->getSeoPath()));
        }
        return Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true, '_query'=>$query));
    }
}