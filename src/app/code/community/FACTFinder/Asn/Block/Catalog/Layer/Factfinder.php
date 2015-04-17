<?php

class FACTFinder_Asn_Block_Catalog_Layer_Factfinder extends Mage_Catalog_Block_Layer_Filter_Attribute
{
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'factfinder_asn/layer_filter_factfinder';
    }
}