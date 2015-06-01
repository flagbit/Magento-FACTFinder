<?php
class FACTFinder_Campaigns_Model_Handler_Product extends FACTFinder_Campaigns_Model_Handler_Abstract
{

    /**
     * Get name of the method to be executed in the adapter
     *
     * @return string
     */
    protected function _getDoParam()
    {
        return 'getProductCampaigns';
    }


    /**
     * Get array of product ids
     *
     * @return array
     */
    protected function _getProductNumberParam()
    {
        if (is_array($this->_productIds)) {
            return current($this->_productIds);
        } else {
            return $this->_productIds;
        }
    }


    /**
     * Get array of campaigns available
     *
     * @return array
     */
    public function getCampaigns()
    {
        $this->_getFacade()->getProductCampaignAdapter()->makeProductCampaign();

        return parent::getCampaigns();
    }


}
