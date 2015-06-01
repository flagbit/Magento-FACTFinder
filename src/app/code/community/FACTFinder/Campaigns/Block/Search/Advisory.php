<?php

class FACTFinder_Campaigns_Block_Search_Advisory extends Mage_Core_Block_Template
{
    /**
     * Search handler
     *
     * @var FACTFinder_Campaigns_Model_Handler_Search
     */
    protected $_searchHandler;


    /**
     * Preparing global layout. Here we initialize the handler
     *
     * @return FACTFinder_Campaigns_Block_Search_Advisory
     */
    protected function _prepareLayout()
    {
        $this->_searchHandler = Mage::getSingleton('factfinder_campaigns/handler_search');
    }


    /**
     * Get Campaign Text
     *
     * @return array
     */
    public function getActiveQuestions()
    {
        $questions = array();

        $_campaigns = $this->_searchHandler->getCampaigns();
        if ($_campaigns && $_campaigns->hasActiveQuestions()) {
            $questions = $_campaigns->getActiveQuestions();
        }

        return $questions;
    }


}
