<?php
class FACTFinder_Campaigns_Model_Facade extends FACTFinder_Core_Model_Facade
{


    /**
     * Get product campaign adapter instance
     *
     * @param string $channel
     *
     * @return \FACTFinder\Adapter\AbstractAdapter
     */
    public function getProductCampaignAdapter($channel = null)
    {
        return $this->_getAdapter("productCampaign", $channel);
    }


    /**
     * Set config params to product campaign adapter
     *
     * @param array  $params
     * @param string $channel
     * @param int    $id
     */
    public function configureProductCampaignAdapter($params, $channel = null, $id = null)
    {
        $this->_configureAdapter($params, "productCampaign", $channel, $id);
    }


    /**
     * Get available product campaigns for the current adapter configuration
     *
     * @param string $channel
     * @param int $id
     *
     * @return Object
     */
    public function getProductCampaigns($channel = null, $id = null)
    {
        return $this->_getFactFinderObject("productCampaign", "getCampaigns", $channel, $id);
    }


}
