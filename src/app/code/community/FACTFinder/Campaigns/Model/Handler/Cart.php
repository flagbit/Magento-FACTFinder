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
 * Class FACTFinder_Campaigns_Model_Handler_Cart
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Model_Handler_Cart extends FACTFinder_Campaigns_Model_Handler_Abstract
{

    /**
     * Get name of the method to be executed in the adapter
     *
     * @return string
     */
    protected function _getDoParam()
    {
        return 'getShoppingCartCampaigns';
    }


    /**
     * Get array of product ids
     *
     * @return array
     */
    protected function _getProductNumberParam()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllItems() as $item) {
            $this->_productIds[] = $item->getProduct()->getData(Mage::helper('factfinder_campaigns')->getIdFieldName());
        }

        return $this->_productIds;
    }


    /**
     * Get array of campaigns available
     *
     * @return array
     */
    public function getCampaigns()
    {
        if (!empty($this->_productIds)) {
            $this->_getFacade()->getProductCampaignAdapter()->makeShoppingCartCampaign();
        }

        return parent::getCampaigns();
    }


}
