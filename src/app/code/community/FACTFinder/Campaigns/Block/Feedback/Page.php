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
 * Class FACTFinder_Campaigns_Block_Feedback_Page
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author tuegeb
 * @copyright Copyright (c) 2016, tuegeb
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 */
class FACTFinder_Campaigns_Block_Feedback_Page extends FACTFinder_Campaigns_Block_Feedback_Abstract
{

    protected $_handlerModel = 'factfinder_campaigns/handler_page';

    /**
     * Check is the campign can be shown on landing page
     *
     * @return bool
     */
    protected function _canBeShown()
    {
        if (!Mage::helper('factfinder_campaigns')->getIsOnLandingPage()
            && !Mage::helper('factfinder_campaigns')->getIsOnStartPage()
        ) {
            return false;
        }
        return (bool) Mage::helper('factfinder')->isEnabled('campaigns');
    }

}