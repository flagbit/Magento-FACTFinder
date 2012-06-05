<?php

class Flagbit_FactFinder_Block_Campaign_Advisory extends Mage_Core_Block_Template
{
    /**
    * get Campaign Text
    *
    * @return string
    */
    public function getActiveQuestions()
    {
        $questions = array();
    
        if(Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign')){
            $_campaigns = Mage::getSingleton('factfinder/adapter')->getCampaigns();
            if($_campaigns && $_campaigns->hasActiveQuestions()){
                $questions = $_campaigns->getActiveQuestions();
            }
        }
    
        return $questions;
    }
}