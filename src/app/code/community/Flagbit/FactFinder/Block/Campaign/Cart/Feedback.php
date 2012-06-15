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
class Flagbit_FactFinder_Block_Campaign_Cart_Feedback extends Mage_Core_Block_Template
{
    /**
    * get campaign questions and answers
    *
    * @return array $feedback
    */
    public function getActiveFeedback()
    {
        Mage::getSingleton('core/session', array('name'=>'frontend'));

        // only display campaign if they are activated and right after a new product was added to cart 
        if (!Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign') || !Mage::getSingleton('checkout/session')->getLastAddedProductId()) {
            return array();
        }
            
        $feedback = array();
        
        $_product = Mage::getModel('catalog/product')->load(Mage::getSingleton('checkout/session')->getLastAddedProductId());
        if (!$_product->getData(Mage::helper('factfinder/search')->getIdFieldName())) {
            return array();
        }
        
        $_campaigns = Mage::helper('factfinder/search')->getProductCampaigns(array(
            $_product->getData(Mage::helper('factfinder/search')->getIdFieldName())
        ));
        
        if($_campaigns && $_campaigns->hasFeedback()){
            $feedback = $_campaigns;
        }
        
        return $feedback;
        
    }
}