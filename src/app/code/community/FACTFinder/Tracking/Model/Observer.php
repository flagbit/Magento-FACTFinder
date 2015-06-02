<?php
class FACTFinder_Tracking_Model_Observer
{
    protected $_facadeModel = 'factfinder_tracking/facade';


    /**
     * Add tracking handle to the layout
     *
     * @param $observer
     */
    public function addTrackingHandles($observer)
    {
        if (!Mage::getStoreConfig('factfinder/export/clicktracking')
            || !Mage::helper('factfinder/search')->getIsOnSearchPage()) {
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
     */
    public function addToCartTracking($observer)
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
            $tracking->trackCart(
                $product->getData($idFieldName),
                $product->getData($idFieldName),
                $product->getName(),
                null,
                md5(Mage::getSingleton('core/session')->getSessionId()),
                null,
                $qty,
                $product->getFinalPrice($qty),
                $customerId
            );
        } catch (Exception $e) {
            Mage::helper('factfinder/debug')->log($e->getMessage());
        }
    }


    /**
     * Tracking of single product click
     *
     * @param \Varien_Event_Observer $observer
     * @internal param $product
     */
    public function sendClickTrackingForRedirectOnSingleResult(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $searchHelper = Mage::helper('factfinder/search');

        if (!Mage::getStoreConfig('factfinder/export/clicktracking')) {
            return;
        }

        try {
            $idFieldName = $searchHelper->getIdFieldName();

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            if ($customerId) {
                $customerId = md5('customer_' . $customerId);
            }

            /* @var $tracking Flagbit_FactFinder_Model_Handler_Tracking */
            $tracking = Mage::getModel('factfinder_tracking/handler_tracking');
            $tracking->trackClick(
                $product->getData($idFieldName),
                $searchHelper->getQuery()->getQueryText(),
                1, // pos,
                $product->getData($idFieldName),
                md5(Mage::getSingleton('core/session')->getSessionId()),
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
            Mage::helper('factfinder/debug')->log($e->getMessage());
        }
    }


    /**
     * Observer method
     * Adds all ordered items to queue that is sent to FACT-Finder by Cronjob.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addOrderDetailsToQueue($observer)
    {
        if (!Mage::getStoreConfigFlag('factfinder/export/track_checkout')) {
            return;
        }

        $order = $observer->getOrder();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $customerId = md5('customer_' . $customerId);
        }

        $searchHelper = Mage::helper('factfinder/search');
        $idFieldName = $searchHelper->getIdFieldName();
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
                    ->setSid(md5(Mage::getSingleton('core/session')->getSessionId()))
                    ->setUserid($customerId)
                    ->setPrice($item->getPrice())
                    ->setCount($item->getQtyOrdered())
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
     */
    public function processOrderQueue()
    {
        $queue = Mage::getModel('factfinder_tracking/queue');

        try {
            foreach ($queue->getCollection() as $item) {
                /* @var $tracking Flagbit_FactFinder_Model_Handler_Tracking */
                $tracking = Mage::getModel('factfinder_tracking/handler_tracking');
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

            if($queue->getCollection()->count()) {
                // We use the last adapter instance to start the parallel request
                $tracking->applyTracking($item->getProductId());
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }


}
