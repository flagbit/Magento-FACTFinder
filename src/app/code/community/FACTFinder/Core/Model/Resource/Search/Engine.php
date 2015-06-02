<?php
class FACTFinder_Core_Model_Resource_Search_Engine extends Mage_CatalogSearch_Model_Resource_Fulltext_Engine
{


    /**
     * Retrieve fulltext search result data collection
     *
     * @return FACTFinder_Core_Model_Resource_Search_Collection
     */
    public function getResultCollection()
    {
        return Mage::getResourceModel('factfinder/search_collection');
    }


}