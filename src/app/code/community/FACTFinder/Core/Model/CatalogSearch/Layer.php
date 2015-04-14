<?php
class FACTFinder_Core_Model_CatalogSearch_Layer extends Mage_CatalogSearch_Model_Layer
{

    /**
     * Get current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getProductCollection()
    {
        if(!Mage::helper('factfinder')->isEnabled()){
            return parent::getProductCollection();
        }

        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = Mage::getResourceModel('factfinder/search_collection');
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }

        return $collection;
    }

}