<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Provides advisory hints to the product view page
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Mike Becker <mike.becker@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_Campaign_Product_Advisory extends Mage_Core_Block_Template
{
    /**
    * get campaign questions and answers
    *
    * @return array $questions
    */
    public function getActiveQuestions()
    {
        $questions = array();
        
        if (Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign') && Mage::registry('current_product')) {
            $_campaigns = Mage::helper('factfinder/search')->getProductCampaigns(array(
                Mage::registry('current_product')->getData(Mage::helper('factfinder/search')->getIdFieldName()),
            ));
            
            if($_campaigns && $_campaigns->hasActiveQuestions()){
                $questions = $_campaigns->getActiveQuestions();
            }
        }
        
        return $questions;
    }
}