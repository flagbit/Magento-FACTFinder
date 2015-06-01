<?php

class FACTFinder_Campaigns_Model_Handler_Search extends FACTFinder_Core_Model_Handler_Search
{

    /**
     * Array of ff campaigns
     *
     * @var array
     */
    protected $_campaigns;


    /**
     * Retrieve redirect url from campaign
     *
     * @return string
     */
    public function getRedirect()
    {
        $url = null;
        $campaigns = $this->getCampaigns();

        if (!empty($campaigns) && $campaigns->hasRedirect()) {
            $url = $campaigns->getRedirectUrl();
        }

        return $url;
    }


    /**
     * Get array of campaigns available
     *
     * @return array
     */
    public function getCampaigns()
    {
        if ($this->_campaigns === null) {
            $this->_campaigns = $this->_getFacade()->getSearchCampaigns();
        }

        return $this->_campaigns;
    }


}
