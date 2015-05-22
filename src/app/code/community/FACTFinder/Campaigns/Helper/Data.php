<?php

class FACTFinder_Campaigns_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @return bool
     */
    public function canShowCampaignsOnProduct()
    {
        return (bool)Mage::app()->getStore()->getConfig('factfinder/config/enable_campaigns_on_prod_page');
    }

}