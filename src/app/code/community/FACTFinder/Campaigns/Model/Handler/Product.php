<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Model_Handler_Product
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
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
        if (!empty($this->_productIds)) {
            $this->_getFacade()->getProductCampaignAdapter()->makeProductCampaign();
        }

        return parent::getCampaigns();
    }


}
