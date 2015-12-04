<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Model_Handler_Search
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
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

        if ($this->_campaigns === null) {
            $this->_campaigns = array();
        }

        return $this->_campaigns;
    }


}
