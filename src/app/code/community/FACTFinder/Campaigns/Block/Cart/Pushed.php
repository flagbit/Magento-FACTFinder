<?php
class FACTFinder_Campaigns_Block_Cart_Pushed extends Mage_Catalog_Block_Product_List_Upsell
{
    const HEADER_LABEL = 'pushed products header';

    /**
     * @return FACTFinder_Campaigns_Block_Cart_Pushed
     */
    protected function _prepareData()
    {
        $this->getItemCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('thumbnail')
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


    /**
     * Get feedback header text
     *
     * @return string
     */
    public function getHeader()
    {
        $campaigns = Mage::getSingleton('factfinder_campaigns/handler_product')->getCampaigns();

        $label = $campaigns->getFeedback(self::HEADER_LABEL);

        if (!empty($label)) {
            return $label;
        }

        return $this->__('Pushed products');
    }


}