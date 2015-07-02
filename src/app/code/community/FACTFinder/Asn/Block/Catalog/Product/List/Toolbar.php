<?php
class FACTFinder_Asn_Block_Catalog_Product_List_Toolbar extends FACTFinder_Core_Block_Catalog_Product_List_Toolbar
{


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        if (!Mage::helper('factfinder_asn')->isCatalogNavigation()) {
            $this->_useFF = false;
        }

        parent::_construct();
    }


}
