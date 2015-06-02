<?php
/**
 * Provides advisory hints to the product view page
 */
class FACTFinder_Campaigns_Block_Product_Advisory extends FACTFinder_Campaigns_Block_Abstract
{

    /**
     * Handler used to access product campaigns data
     *
     * @var FACTFinder_Campaigns_Model_Handler_Product
     */
    protected $_productCampaignHandler;


    /**
     * Preparing global layout
     *
     * @return FACTFinder_Campaigns_Block_Product_Advisory
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('factfinder')->isEnabled('campaigns')) {
            return parent::_prepareLayout();
        }

        $productIds = array(
            Mage::registry('current_product')->getData(Mage::helper('factfinder/search')->getIdFieldName())
        );

        $this->_productCampaignHandler = Mage::getSingleton('factfinder_campaigns/handler_product', $productIds);

        return parent::_prepareLayout();
    }


    /**
    * Get campaign questions and answers
    *
    * @return array
    */
    public function getActiveQuestions()
    {
        $questions = array();

        if ($this->canCampaignBeDisplay()) {
            $questions = $this->_productCampaignHandler->getActiveAdvisorQuestions();
        }

        return $questions;
    }


    /**
     * Check if campaign can be displayed
     *
     * @return bool
     */
    protected function canCampaignBeDisplay()
    {
        return (bool) Mage::registry('current_product');
    }


}