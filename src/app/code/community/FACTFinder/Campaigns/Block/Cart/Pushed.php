<?php
class FACTFinder_Campaigns_Block_Cart_Pushed extends Mage_Catalog_Block_Product_List_Upsell
{


    /**
     * @return FACTFinder_Campaigns_Block_Cart_Pushed
     */
    protected function _prepareData()
    {
        $this->getItemCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('small_image')
            ->addPriceData();

        return $this;
    }


    /**
     * Get pushed products collection
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function getItemCollection()
    {
        if ($this->_itemCollection === null) {
            $this->_itemCollection = Mage::getResourceModel('factfinder_campaigns/pushedproducts_collection');
            $this->_itemCollection->setHandler(Mage::getSingleton('factfinder_campaigns/handler_cart'));
        }

        return $this->_itemCollection;
    }


}