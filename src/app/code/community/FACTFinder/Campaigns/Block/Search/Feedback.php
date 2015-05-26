<?php

class FACTFinder_Campaigns_Block_Search_Feedback extends Mage_Core_Block_Template
{
    /**
     * Pushed Products Collection
     *
     * @var FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    protected $_pushedProductsCollection = null;


    /**
     * @var FACTFinder_Campaigns_Model_Handler_Search
     */
    protected $_searchHandler;

    protected function _prepareLayout()
    {
        $this->_searchHandler = Mage::getSingleton('factfinder_campaigns/handler_search');

        return parent::_prepareLayout();
    }


    /**
     * Get Campaign Text
     *
     * @return string
     */
    public function getText()
    {
        $text = '';

        $_campaigns = $this->_searchHandler->getCampaigns();
        if ($_campaigns && $_campaigns->hasFeedback() && $this->getTextNumber()) {
            $text = $_campaigns->getFeedback($this->getTextNumber() - 1);
        }

        return $text;
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
        }

        return $this->_pushedProductsCollection;
    }


}