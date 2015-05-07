<?php

class FACTFinder_Tracking_Model_Observer
{
    /**
     * Observer method.
     * Sends information to FACT-Finder if item was added to cart.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addToCartSendSCIC($observer)
    {
        if (!Mage::getStoreConfigFlag('factfinder/export/track_carts')) {
            return;
        }

        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();

        $searchHelper = Mage::helper('factfinder/search');
        $idFieldName = $searchHelper->getIdFieldName();

        $qty = $quoteItem->getQty();

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if ($customerId) {
            $customerId = md5('customer_' . $customerId);
        }

        try {
            /* @var $tracking FACTFinder_Tracking_Model_Handler_Tracking */
            $tracking = Mage::getModel('factfinder_tracking/handler_tracking');
            $tracking->getTrackingAdapter()->setupEventTracking(
                'cart',
                array(
                    'id'           => $product->getData($idFieldName),
                    'sid'          => md5(Mage::getSingleton('core/session')->getSessionId()),
                    'amount'       => $qty,
                    'price'        => $product->getFinalPrice($qty),
                    'uid'          => $customerId,
                    'site'         => Mage::app()->getStore()->getCode(),
                    'sourceRefKey' => Mage::getSingleton('core/session')->getFactFinderRefKey()
                )
            );
            $tracking->applyTracking();
        } catch (Exception $e) {
            Mage::helper('factfinder/debug')->log($e->getMessage());
        }
    }

}