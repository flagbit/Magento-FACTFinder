<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Model class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Model_Observer
{

    protected $_facadeModel = 'factfinder_tracking/facade';


    /**
     * Add tracking handle to the layout
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function addTrackingHandles($observer)
    {
        if (!Mage::getStoreConfig('factfinder/export/clicktracking')
            || !Mage::helper('factfinder/search')->getIsOnSearchPage()
            || !Mage::helper('factfinder')->isEnabled('tracking')
        ) {
            return;
        }

        $layout = $observer->getLayout();
        $update = $layout->getUpdate();
        $update->addHandle('factfinder_clicktracking_enabled');
    }


    /**
     * Observer method.
     * Sends information to FACT-Finder if item was added to cart.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function addToCartTracking($observer)
    {
        if (!Mage::getStoreConfigFlag('factfinder/export/track_carts')
            || !Mage::helper('factfinder')->isEnabled('tracking')
        ) {
            return;
        }

        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $observer->getQuoteItem();

        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getProduct();

        $idFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();

        $qty = $quoteItem->getQty();

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if ($customerId) {
            $customerId = md5('customer_' . $customerId);
        }

        try {
            /** @var $tracking FACTFinder_Tracking_Model_Handler_Tracking */
            $tracking = Mage::getModel('factfinder_tracking/handler_tracking');
            $tracking->trackCart(
                $quoteItem->getProductId(),
                $product->getData($idFieldName),
                $product->getName(),
                null,
                Mage::helper('factfinder_tracking')->getSessionId(),
                null,
                $qty,
                $product->getFinalPrice($qty),
                $customerId
            );
        } catch (Exception $e) {
            Mage::helper('factfinder/debug')->error($e->getMessage());
        }
    }


    /**
     * Tracking of single product click
     *
     * @param \Varien_Event_Observer $observer
     *
     * @internal param $product
     */
    public function sendClickTrackingForRedirectOnSingleResult(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $searchHelper = Mage::helper('factfinder/search');

        if (!Mage::getStoreConfig('factfinder/export/clicktracking')
            || !Mage::helper('factfinder')->isEnabled('tracking')
        ) {
            return;
        }

        try {
            $idFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            if ($customerId) {
                $customerId = md5('customer_' . $customerId);
            }

            /** @var FACTFinder_Tracking_Model_Handler_Tracking $tracking */
            $tracking = Mage::getModel('factfinder_tracking/handler_tracking');
            $tracking->trackClick(
                $product->getData($idFieldName),
                $searchHelper->getQuery()->getQueryText(),
                1, // pos,
                $product->getData($idFieldName), // master id but there's none on redirect
                Mage::helper('factfinder_tracking')->getSessionId(),
                null,
                1, // origPos
                1, // page
                $product->getSimilarity(),
                $product->getName(),
                $searchHelper->getPageLimit(),
                $searchHelper->getDefaultPerPageValue(),
                $customerId
            );
        }
        catch (Exception $e) {
            Mage::helper('factfinder/debug')->error($e->getMessage());
        }
    }


    /**
     * Observer method
     * Adds all ordered items to queue that is sent to FACT-Finder by Cronjob.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function addOrderDetailsToQueue($observer)
    {
        if (!Mage::getStoreConfigFlag('factfinder/export/track_checkout')
            || !Mage::helper('factfinder')->isEnabled('tracking')
        ) {
            return;
        }

        $order = $observer->getOrder();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $customerId = md5('customer_' . $customerId);
        }

        $idFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();
        if ($idFieldName == 'entity_id') {
            $idFieldName = 'product_id'; // sales_order_item does not contain a entity_id
        }

        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem() != null) {
                continue;
            }

            try {
                Mage::getModel('factfinder_tracking/queue')
                    ->setProductId($item->getData($idFieldName))
                    ->setProductName($item->getName())
                    ->setSid(Mage::helper('factfinder_tracking')->getSessionId())
                    ->setUserid($customerId)
                    ->setPrice($item->getPrice())
                    ->setCount($item->getQtyOrdered())
                    ->setStoreId($order->getStoreId())
                    ->save();
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }


    /**
     * Cronjob observer method.
     * Processes all orders given in queue and sends them to FACT-Finder.
     *
     * @return void
     */
    public function processOrderQueue()
    {
        if (!Mage::helper('factfinder')->isEnabled('tracking')) {
            return;
        }

        $queue = Mage::getModel('factfinder_tracking/queue');

        try {
            $itemsByStore = array();
            foreach ($queue->getCollection() as $item) {
                $itemsByStore[$item->getStoreId()][] = $item;
            }

            foreach ($itemsByStore as $storeId => $items) {
                /** @var FACTFinder_Tracking_Model_Handler_Tracking $tracking */
                $tracking = Mage::getModel('factfinder_tracking/handler_tracking');
                $tracking->setStoreId($storeId);

                foreach ($items as $item) {
                    $tracking->setupCheckoutTracking(
                        $item->getProductId(),
                        $item->getProductId(),
                        $item->getProductName(),
                        null,
                        $item->getSid(),
                        null,
                        $item->getCount(),
                        $item->getPrice(),
                        $item->getUserid()
                    );

                    $item->delete($item);
                }

                $tracking->applyTracking($item->getProductId());
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Store initial session id to be able to recognize the user sill after login
     *
     * @param Varien_Event_Observer $observer
     */
    public function storeSessionId(Varien_Event_Observer $observer)
    {
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->getData('ff_session_id')) {
            $customerSession->setData('ff_session_id', $customerSession->getEncryptedSessionId());
        }
    }

    public function loginTracking(Varien_Event_Observer $observer)
    {
        if(!Mage::getStoreConfigFlag('factfinder/config/personalization')) {
            return;
        }

        $customer = $observer->getCustomer();
        if($customer->getId()) {

            $customerId = md5('customer_' . $customer->getId());

            /** @var $tracking Flagbit_FactFinder_Model_Handler_Tracking */
            $tracking = Mage::getModel('factfinder_tracking/handler_tracking');
            $tracking->trackLogin(
                Mage::helper('factfinder_tracking')->getSessionId(),
                null,
                $customerId
            );
        }
    }
}
