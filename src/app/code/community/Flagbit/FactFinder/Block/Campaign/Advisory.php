<?php

class Flagbit_FactFinder_Block_Campaign_Advisory extends Mage_Core_Block_Template
{
    protected $_searchHandler;

    protected function _prepareLayout()
    {
        if(Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign')){
            $this->_searchHandler = Mage::getSingleton('factfinder/handler_search');
        }
    }

    /**
    * get Campaign Text
    *
    * @return string
    */
    public function getActiveQuestions()
    {
        $questions = array();
    
        if(Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign')){
            $_campaigns = $this->_searchHandler->getCampaigns();
            if($_campaigns && $_campaigns->hasActiveQuestions()){
                $questions = $_campaigns->getActiveQuestions();
            }
        }
    
        return $questions;
    }
}