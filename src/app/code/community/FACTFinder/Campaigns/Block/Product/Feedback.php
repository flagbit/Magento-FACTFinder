<?php

/**
 * Provides advisory hints to the product view page
 */
class FACTFinder_Campaigns_Block_Product_Feedback extends Mage_Core_Block_Template
{

    /**
     * @var FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    protected $_pushedProductsCollection;


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
     * get campaign feedback
     *
     * @return array $feedback
     */
    public function getActiveFeedback()
    {
        $feedback = array();

        if (Mage::registry('current_product') && Mage::helper('factfinder_campaigns')->canShowCampaignsOnProduct()) {
            $_campaigns = $this->_productCampaignHandler->getCampaigns();

            if ($_campaigns && $_campaigns->hasFeedback()) {
                $feedback = $_campaigns;
            }
        }

        return $feedback;
    }


    /**
     * Pushed Products Collection
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function getPushedProductsCollection()
    {
        if ($this->_pushedProductsCollection === null) {
            $this->_pushedProductsCollection = Mage::getResourceModel('factfinder_campaigns/pushedproducts_collection');
            $this->_pushedProductsCollection->setHandler($this->_productCampaignHandler);
        }

        return $this->_pushedProductsCollection;
    }
}