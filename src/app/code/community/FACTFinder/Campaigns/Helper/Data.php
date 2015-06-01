<?php

class FACTFinder_Campaigns_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * Check config if showing campaigns on product page is enabled
     *
     * @return bool
     */
    public function canShowCampaignsOnProduct()
    {
        return (bool)Mage::app()->getStore()->getConfig('factfinder/config/enable_campaigns_on_prod_page');
    }


}