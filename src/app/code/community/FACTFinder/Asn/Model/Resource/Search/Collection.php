<?php

class FACTFinder_Asn_Model_Resource_Search_Collection extends FACTFinder_Core_Model_Resource_Search_Collection
{

    /**
     * Get FACT-Finder Facade
     *
     * @return FACTFinder_Core_Model_Handler_Search
     */
    protected function _getSearchHandler()
    {
        return Mage::getSingleton('factfinder_asn/handler_search');
    }

}