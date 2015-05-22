<?php
class FACTFinder_Campaigns_Model_Handler_Cart extends FACTFinder_Campaigns_Model_Handler_Abstract
{
    protected function _getDoParam()
    {
        return 'getShoppingCartCampaigns';
    }

    protected function _getProductNumberParam()
    {
        if (is_array($this->_productIds)) {
            return $this->_productIds;
        } else {
            return array($this->_productIds);
        }
    }

    public function getCampaigns()
    {
        $this->_getFacade()->getProductCampaignAdapter()->makeShoppingCartCampaign();

        return parent::getCampaigns();
    }
}