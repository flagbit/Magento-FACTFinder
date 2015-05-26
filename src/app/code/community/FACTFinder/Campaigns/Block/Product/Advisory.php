<?php
/**
 * Provides advisory hints to the product view page
 */
class FACTFinder_Campaigns_Block_Product_Advisory extends Mage_Core_Block_Template
{

    /**
     * @var FACTFinder_Campaigns_Model_Handler_Product
     */
    protected $_productCampaignHandler;

    protected function _prepareLayout()
    {
        $productIds = array(
            Mage::registry('current_product')->getData(Mage::helper('factfinder/search')->getIdFieldName())
        );

        $this->_productCampaignHandler = Mage::getSingleton('factfinder_campaigns/handler_product', $productIds);

        return parent::_prepareLayout();
    }

    /**
    * get campaign questions and answers
    *
    * @return array $questions
    */
    public function getActiveQuestions()
    {
        $questions = array();

        if ($this->canCampaignBeDisplay()) {
            $questions = $this->_productCampaignHandler->getActiveAdvisorQuestions();
        }

        return $questions;
    }

    protected function canCampaignBeDisplay()
    {
        return (bool) Mage::registry('current_product');
    }
}