<?php
class FACTFinder_Campaigns_Model_Handler_Cart extends FACTFinder_Campaigns_Model_Handler_Abstract
{

    /**
     * Get name of the method to be executed in the adapter
     *
     * @return string
     */
    protected function _getDoParam()
    {
        return 'getShoppingCartCampaigns';
    }


    /**
     * Get array of product ids
     *
     * @return array
     */
    protected function _getProductNumberParam()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $productIds = array();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllItems() as $item) {
            $productIds[] = $item->getProduct()->getData(Mage::helper('factfinder/search')->getIdFieldName());
        }

        return $productIds;
    }


    /**
     * Get array of campaigns available
     *
     * @return array
     */
    public function getCampaigns()
    {
        $this->_getFacade()->getProductCampaignAdapter()->makeShoppingCartCampaign();

        return parent::getCampaigns();
    }


}
