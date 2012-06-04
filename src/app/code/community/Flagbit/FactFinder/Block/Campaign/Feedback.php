<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Block class
 *
 * This class is used to disable Magento´s default Price and Category Filter Output
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: Scic.php 645 2011-03-17 14:54:20Z weller $
 */
class Flagbit_FactFinder_Block_Campaign_Feedback extends Mage_Core_Block_Template
{
    /**
     * Pushed Products Collection
     *
     * @var Flagbit_FactFinder_Model_Mysql4_Campaign_Pushedproducts_Collection
     */
    protected $_pushedProductsCollection = null;

    /**
     * get Campaign Text
     *
     * @return string
     */
    public function getText()
    {
        $text = '';
        
        if(Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign')){
            $_campaigns = Mage::getSingleton('factfinder/adapter')->getCampaigns();
            if($_campaigns && $_campaigns->hasFeedback() && $this->getTextNumber()){
                $text = $_campaigns->getFeedback($this->getTextNumber() - 1);
            }
        }
        
        return $text;
    }

    /**
     * Pushed Products Collection
     *
     * @return Flagbit_FactFinder_Model_Mysql4_Campaign_Pushedproducts_Collection
     */
    public function getPushedProductsCollection()
    {
        if($this->_pushedProductsCollection === null){
            $this->_pushedProductsCollection = Mage::getResourceModel('factfinder/campaign_pushedproducts_collection');
        }

        return $this->_pushedProductsCollection;
    }
}