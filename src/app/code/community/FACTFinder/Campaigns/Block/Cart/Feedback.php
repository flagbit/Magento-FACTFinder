<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Provides feedback text to the cart page
 */
class FACTFinder_Campaigns_Block_Cart_Feedback extends FACTFinder_Campaigns_Block_Abstract
{

    /**
     * Handler used to get data from ff
     *
     * @var FACTFinder_Campaigns_Model_Handler_Cart
     */
    protected $_handler;


    /**
     * Preparing global layout
     *
     * @return FACTFinder_Campaigns_Block_Cart_Feedback
     */
    protected function _prepareLayout()
    {
        if (Mage::helper('factfinder')->isEnabled('campaigns')) {
            $this->_handler = Mage::getSingleton('factfinder_campaigns/handler_cart');
        }

        return parent::_prepareLayout();
    }


    /**
     * Get campaign questions and answers
     *
     * @return array
     */
    public function getActiveFeedback()
    {
        $feedback = array();

        $_campaigns = $this->_handler->getCampaigns();

        if ($_campaigns && $_campaigns->hasFeedback()) {
            $feedback = $_campaigns;
        }

        return $feedback;
    }


}