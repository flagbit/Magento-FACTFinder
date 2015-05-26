<?php
class FACTFinder_Campaigns_Model_Handler_Product extends FACTFinder_Campaigns_Model_Handler_Abstract
{
    protected function _getDoParam()
    {
        return 'getProductCampaigns';
    }

    protected function _getProductNumberParam()
    {
        if (is_array($this->_productIds)) {
            return current($this->_productIds);
        } else {
            return $this->_productIds;
        }
    }

    public function getCampaigns()
    {
        $this->_getFacade()->getProductCampaignAdapter()->makeProductCampaign();

        return parent::getCampaigns();
    }
}