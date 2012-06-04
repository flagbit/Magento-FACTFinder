<?php 
class Flagbit_FactFinder_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item {
    
    
    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {    
        if ($this->getFilter()->getRequestVar() == 'Category' && $this->getValue() != '') {
            $query = array($this->getFilter()->getRequestVar()=>$this->getValue());
            $params['_current']     = true;
            $params['_use_rewrite'] = true;
            $params['_query']       = $query;
            $params['_escape']      = true;
            return Mage::getUrl('*/*/*', $params);
        } else {
            return parent::getRemoveUrl();
        }
    }
}