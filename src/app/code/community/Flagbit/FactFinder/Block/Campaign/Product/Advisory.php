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
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Mike Becker <mike.becker@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_Campaign_Product_Advisory extends Mage_Core_Block_Template
{
    /**
    * get Campaign Text
    *
    * @return string
    */
    public function getActiveQuestions()
    {
        $questions = array();
    
        $productCampaignAdapter = Mage::getModel('factfinder/adapter')->getProductCampaignAdapter();
        // set current productid
        $productCampaignAdapter->setProductIds(array(Mage::registry('current_product')->getId()));
        $productCampaignAdapter->makeProductCampaign();
        
        if(Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign')){
            $_campaigns = Mage::getSingleton('factfinder/adapter')->getProductCampaigns();
            $_campaigns = $productCampaignAdapter->getCampaigns();
            Zend_Debug::dump(array(
                'class' => __CLASS__,
                'method' => __FUNCTION__,
                'campaigns' => $_campaigns
            ));
            if($_campaigns && $_campaigns->hasActiveQuestions()){
                $questions = $_campaigns->getActiveQuestions();
            }
        }
    
        return $questions;
    }
}