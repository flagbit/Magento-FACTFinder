<?php
class FACTFinder_Campaigns_Model_Facade extends FACTFinder_Core_Model_Facade
{
    public function getProductCampaignAdapter($channel = null)
    {
        return $this->_getAdapter("productCampaign", $channel);
    }

    public function configureProductCampaignAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "productCampaign", $channel, $id);
    }

    public function getCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("search", "getCampaigns", $channel, $id);
    }

    public function getProductCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("productCampaign", "getCampaigns", $channel, $id);
    }
}