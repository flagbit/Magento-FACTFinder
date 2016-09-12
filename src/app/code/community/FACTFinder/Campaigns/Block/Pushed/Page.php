<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author tuegeb
 * @copyright Copyright (c) 2016, tuegeb
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 */

/**
 * Class FACTFinder_Campaigns_Block_Pushed_Page
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author tuegeb
 * @copyright Copyright (c) 2016, tuegeb
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 */
class FACTFinder_Campaigns_Block_Pushed_Page extends FACTFinder_Campaigns_Block_Pushed_Abstract
{

    protected $_handlerModel = 'factfinder_campaigns/handler_page';

    /**
     * Check is the campign can be shown on landing page
     *
     * @return bool
     */
    protected function _canBeShown()
    {
        if (!Mage::registry('current_category')
           || Mage::registry('current_category')->getDisplayMode() === Mage_Catalog_Model_Category::DM_PRODUCT 
           || !Mage::helper('factfinder_campaigns')->canShowLandingPageCampaigns())
        {
            return false;
        }
        return (bool) Mage::helper('factfinder')->isEnabled('campaigns');
    }

}