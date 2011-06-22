<?php

class Flagbit_FactFinder_Model_Layer extends Flagbit_FactFinder_Model_Layer_Abstract
{
    const XML_PATH_DISPLAY_LAYER_COUNT    = 'catalog/search/use_layered_navigation_count';

    protected $_productCollection = null;
    
    /**
     * Get current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getProductCollection()
    {
        if(!Mage::helper('factfinder/search')->getIsEnabled()){
            return parent::getProductCollection();
        }

        // handle search
        if($this instanceof Mage_CatalogSearch_Model_Layer){
            if(is_null($this->_productCollection)){
			    $this->_productCollection = Mage::getResourceModel('factfinder/search_collection');
			    $this->prepareProductCollection($this->_productCollection);
            }
            $collection = $this->_productCollection;
                    
        // handle category listing    
        }else{
            if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
                $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
            }
            else {
                //$collection = $this->getCurrentCategory();
                $collection = Mage::getResourceModel('factfinder/search_collection');
                $this->prepareProductCollection($collection);
                $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
            }
        }        
        

        return $collection;
    }

    /**
     * Get collection of all filterable attributes for layer products set
     *
     * @return Flagbit_FactFinder_Model_Mysql4_Product_Attribute_Collection
     */
    public function getFilterableAttributes()
    {

        if(!Mage::helper('factfinder/search')->getIsEnabled()){
            return parent::getFilterableAttributes();
        }

        /* @var $collection Flagbit_FactFinder_Model_Mysql4_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('factfinder/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setStoreId(Mage::app()->getStore()->getId());

        return $collection;
    }
}
