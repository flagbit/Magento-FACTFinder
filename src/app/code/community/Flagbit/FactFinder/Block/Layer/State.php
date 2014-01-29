<?php
class Flagbit_FactFinder_Block_Layer_State extends Mage_Catalog_Block_Layer_State
{
    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        Mage::log($this->getRequest()->getParams(), null, 'filter_debug.log');
        $currentParams = $this->getRequest()->getParams();
        foreach($currentParams as $paramKey => $paramValue) {
            if($paramKey != 'q') {
                $currentParams[$paramKey] = null;
            }
        }
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $currentParams;
        $params['_escape']      = true;
        return Mage::getUrl('*/*/*', $params);
    }
}
