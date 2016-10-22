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
 * Class FACTFinder_Campaigns_Model_Handler_Page
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author tuegeb
 * @copyright Copyright (c) 2016, tuegeb
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 */
class FACTFinder_Campaigns_Model_Handler_Page extends FACTFinder_Campaigns_Model_Handler_Abstract
{
    /**
     * Class constructor
     *
     * @param string $pageId
     */
    public function __construct($pageId)
    {
        $this->_pageId = $pageId;
        parent::__construct();
    }

    /**
     * Get name of the method to be executed in the adapter
     *
     * @return string
     */
    protected function _getDoParam()
    {
        return 'getPageCampaigns';
    }

    /**
     * Get page id
     *
     * @return string
     */
    protected function _getPageIdParam()
    {
        if (Mage::helper('factfinder_campaigns')->getIsOnLandingPage()) {
            $this->_pageId = Mage::registry('current_category')->getId();
        } else if (Mage::helper('factfinder_campaigns')->getIsOnStartPage()) {
            $this->_pageId = 'start';
        }
        return $this->_pageId;
    }

    /**
     * Get array of campaigns available
     *
     * @return array
     */
    public function getCampaigns()
    {
        if (isset($this->_pageId)) {
            $this->_getFacade()->getProductCampaignAdapter()->makePageCampaign();
            return parent::getCampaigns();
        }
        return array();
    }

}
