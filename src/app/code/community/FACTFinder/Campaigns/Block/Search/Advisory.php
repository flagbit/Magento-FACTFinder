<?php

class FACTFinder_Campaigns_Block_Search_Advisory extends Mage_Core_Block_Template
{
    /**
     * @var FACTFinder_Campaigns_Model_Handler_Search
     */
    protected $_searchHandler;

    protected function _prepareLayout()
    {
        $this->_searchHandler = Mage::getSingleton('factfinder_campaigns/handler_search');
    }

    /**
     * get Campaign Text
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
