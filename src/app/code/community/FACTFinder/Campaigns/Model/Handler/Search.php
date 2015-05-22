<?php

class FACTFinder_Campaigns_Model_Handler_Search extends FACTFinder_Core_Model_Handler_Search
{
    protected $_facadeModel = 'factfinder_campaigns/facade';

    protected $_campaigns;


    public function getRedirect()
    {
        $url = null;
        $campaigns = $this->getCampaigns();

        if (!empty($campaigns) && $campaigns->hasRedirect()) {
            $url = $campaigns->getRedirectUrl();
        }

        return $url;
    }

    public function getCampaigns()
    {
        if ($this->_campaigns === null) {
            $this->_campaigns = $this->_getFacade()->getCampaigns();
        }

        return $this->_campaigns;
    }
}