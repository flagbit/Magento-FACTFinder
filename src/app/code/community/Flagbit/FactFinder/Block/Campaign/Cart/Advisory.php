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
class Flagbit_FactFinder_Block_Campaign_Cart_Advisory extends Mage_Core_Block_Template
{
    /**
    * get campaign questions and answers
    *
    * @return array $questions
    */
    public function getActiveQuestions()
    {
        Mage::getSingleton('core/session', array('name'=>'frontend'));

        // only display campaign right after a new product was added to cart 
        if (!Mage::getStoreConfig('factfinder/activation/campaign') || !Mage::getSingleton('checkout/session')->getLastAddedProductId()) {
            return array();
        }
            
        $questions = array();
        
        $_product = Mage::getModel('catalog/product')->load(Mage::getSingleton('checkout/session')->getLastAddedProductId());
        if (!$_product->getSku()) {
            return array();
        }
        
        // get productcampaign adapter and set current sku
        $productCampaignAdapter = Mage::getModel('factfinder/adapter')->getProductCampaignAdapter();
        $productCampaignAdapter->setProductIds(array($_product->getSku()));
        $productCampaignAdapter->makeProductCampaign();
        
        $_campaigns = $productCampaignAdapter->getCampaigns();

        if($_campaigns && $_campaigns->hasActiveQuestions()){
            $questions = $_campaigns->getActiveQuestions();
        }
    
        return $questions;
    }
}