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
class Flagbit_FactFinder_Block_Campaign_Product_Feedback extends Mage_Core_Block_Template
{
    /**
    * get campaign feedback
    *
    * @return array $feedback
    */
    public function getActiveFeedback()
    {
        $feedback = array();
        
        if (Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign') && Mage::registry('current_product')) {
            $_campaigns = Mage::helper('factfinder/search')->getProductCampaigns(array(
                Mage::registry('current_product')->getData(Mage::helper('factfinder/search')->getIdFieldName()),
            ));
            
            if($_campaigns && $_campaigns->hasFeedback()){
                $feedback = $_campaigns;
            }
        }

        return $feedback;
    }
}