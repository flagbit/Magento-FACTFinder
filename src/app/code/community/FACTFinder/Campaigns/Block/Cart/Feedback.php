<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Provides feedback text to the cart page
 */
class FACTFinder_Campaigns_Block_Cart_Feedback extends FACTFinder_Campaigns_Block_Abstract
{

    /**
     * Collection of "pushed" products
     *
     * @var FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    protected $_pushedProductsCollection;

    /**
     * Current product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Handler used to get data from ff
     *
     * @var FACTFinder_Campaigns_Model_Handler_Cart
     */
    protected $_handler;


    /**
     * Preparing global layout
     *
     * @return FACTFinder_Campaigns_Block_Cart_Feedback
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('factfinder')->isEnabled('campaigns')) {
            return parent::_prepareLayout();
        }

        $productId = Mage::getSingleton('checkout/session')->getLastAddedProductId();

        if ($productId) {
            $this->_product = Mage::getModel('catalog/product')->load(
                Mage::getSingleton('checkout/session')->getLastAddedProductId()
            );

            $productIds = array(
                $this->_product->getData(Mage::helper('factfinder/search')->getIdFieldName())
            );

            $this->_handler = Mage::getSingleton('factfinder_campaigns/handler_cart', $productIds);
        }

        return parent::_prepareLayout();
    }


    /**
     * Get campaign questions and answers
     *
     * @return array
     */
    public function getActiveFeedback()
    {
        Mage::getSingleton('core/session', array('name' => 'frontend'));

        // only display campaign if they are activated and right after a new product was added to cart
        if (!Mage::getSingleton('checkout/session')->getLastAddedProductId()) {
            return array();
        }

        $feedback = array();

        if (!$this->_product->getData(Mage::helper('factfinder/search')->getIdFieldName())) {
            return array();
        }

        $_campaigns = $this->_handler->getCampaigns();

        if ($_campaigns && $_campaigns->hasFeedback()) {
            $feedback = $_campaigns;
        }

        return $feedback;
    }


    /**
     * Pushed Products Collection
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function getPushedProductsCollection()
    {
        if ($this->_pushedProductsCollection === null && $this->_handler) {
            $this->_pushedProductsCollection = Mage::getResourceModel('factfinder_campaigns/pushedproducts_collection');
            $this->_pushedProductsCollection->setHandler($this->_handler);
        }

        return $this->_pushedProductsCollection;
    }

}