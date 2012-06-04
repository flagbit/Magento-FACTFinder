<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Model class
 *
 * Observer for Magento events.
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Michael Türk <tuerk@flagbit.de>
 * @version   $Id: Processor.php 647 2011-03-21 10:32:14Z rudolf_batt $
 */
class Flagbit_FactFinder_Model_Observer
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
            $scic = Mage::getModel('factfinder/adapter')->getScicAdapter();
            $result = $scic->trackCart($product->getData($idFieldName), md5(Mage::getSingleton('core/session')->getSessionId()), $qty, $product->getFinalPrice($qty), $customerId);
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
    public function addOrderDetailsToSCICQueue($observer)
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
                Mage::getModel('factfinder/scic_queue')
                    ->setProductId($item->getData($idFieldName))
                    ->setSid(md5(Mage::getSingleton('core/session')->getSessionId()))
                    ->setUserid($customerId)
                    ->setPrice($item->getPrice())
                    ->setCount($item->getQtyOrdered())
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->save();
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }


    /**
     * Cronjob observer method.
     * Processes all orders given in SCIC queue and sends them to FACT-Finder.
     *
     */
    public function processScicOrderQueue()
    {
        $queue = Mage::getModel('factfinder/scic_queue');
        $collection = $queue->getCollection()->addOrder('store_id', 'ASC');

        $storeId = null;
        $scic = null;
        foreach ($collection as $item) {
            try {
                if ($item->getStoreId() != $storeId) {
                    $scic = Mage::getModel('factfinder/adapter')->setStoreId($item->getStoreId())->getScicAdapter();
                    $storeId = $item->getStoreId();
                }

                $scic->trackCheckout($item->getProductId(), $item->getSid(), $item->getCount(), $item->getPrice(), $item->getUserid());
                $item->delete($item);
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }


    /**
     * Checks configuration data before saving it to database.
     *
     * @param Varien_Event_Observer $observer
     */
    public function setEnabledFlagInFactFinderConfig($observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        if ($request->getParam('section') != 'factfinder') {
            return;
        }

        $groups = $request->getPost('groups');
        $website = $request->getParam('website');
        $store   = $request->getParam('store');

        if (
               is_array($groups['search'])
            && is_array($groups['search']['fields'])
            && is_array($groups['search']['fields']['enabled'])
            && isset($groups['search']['fields']['enabled']['value'])
        ) {
            $value = $groups['search']['fields']['enabled']['value'];
        }
        elseif ($store) {
            $value = Mage::app()->getWebsite($website)->getConfig('factfinder/search/enabled');
        }
        else {
            $value = (string) Mage::getConfig()->getNode('default/factfinder/search/enabled');
        }

        if (!$value) {
            return;
        }

        $errors = Mage::helper('factfinder/backend')->checkConfigData($groups['search']['fields']);
        if (!empty($errors)) {
        	$groups['search']['fields']['enabled']['errors'] = $errors;
        }

        // if we have an error - unset inherit field so that Backend model processing is activated
        if (!empty($errors) && isset($groups['search']['fields']['enabled']['inherit'])) {
        	unset($groups['search']['fields']['enabled']['inherit']);
        	$groups['search']['fields']['enabled']['value'] = $value;
        }

        $request->setPost('groups', $groups);
    }
    
    
    /**
     * Replaces the link to the management cockpit functionality in the Magento Backend with the external link that
     * opens in a new browser tab. Pretty dirty solution, but Magento does not offer any possibility to edit link urls
     * in its backend menu model, nor does it allow to add absolute links for external sites.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function rewriteBackendMenuHtmlForCockpitRedirect($observer)
    {
        $block = $observer->getBlock();
        if ($block->getNameInLayout() != 'menu') {
            return;
        }
        
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        
        $matches = array();
        $label = preg_quote(Mage::helper('factfinder')->__('FACT-Finder Business User Cockpit'));
        $pattern = '/(\<a[^\>]*href=\"([^\"]*)\"[^\>]*)\>\w*\<span\>\w*' . $label . '\w*\<\/span\>/msU';
        preg_match($pattern, $html, $matches);
        
        $url = Mage::getSingleton('factfinder/adapter')->getAuthenticationUrl();
        $replace = str_replace($matches[2], $url, $matches[1]) . ' target="_blank"';
        
        $transport->setHtml(str_replace($matches[1], $replace, $html));
    }
    
    /**
    * Adds layout handles based on FACT-Finder configuration.
    * 
    * @param Varien_Event_Observer $observer
    */
    public function addActivationLayoutHandles($observer)
    {
        if (Mage::helper('factfinder/search')->getIsEnabled(false, 'suggest')) {
            $layout = $observer->getLayout();
            $update = $layout->getUpdate();
            $update->addHandle('factfinder_suggest_enabled');
        }
        if (Mage::helper('factfinder/search')->getIsEnabled(false, 'advisory')) {
            $layout = $observer->getLayout();
            $update = $layout->getUpdate();
            $update->addHandle('factfinder_advisory_enabled');
        }
        $request = Mage::app()->getRequest();
        //catalogsearch_result_index
        if (Mage::helper('factfinder/search')->getIsEnabled(false, 'clicktracking')
                && $request->getModuleName() == 'catalogsearch'
                && $request->getControllerName() == 'result'
                && $request->getActionName() == 'index') {
            $layout = $observer->getLayout();
            $update = $layout->getUpdate();
            $update->addHandle('factfinder_clicktracking_enabled');
        }
    }
    
    /**
     * Checks if the result set's size is one. If so the user is redirected to the product detail page. This is checked
     * right before the first block is rendered so headers can still be sent. The ordinary collection load event is 
     * triggered too late.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function redirectToProductIfSingleResult($observer)
    {
        if (!Mage::helper('factfinder/search')->getIsEnabled() || !Mage::helper('factfinder/search')->getIsOnSearchPage() || Mage::registry('redirectAlreadyChecked')) {
            return;
        }
        
        Mage::register('redirectAlreadyChecked', 1);
        
        if (Mage::getStoreConfig('factfinder/config/redirectOnSingleResult')) {
            $block = Mage::app()->getLayout()->getBlock('search_result_list');
            
            if (!$block instanceof Mage_Catalog_Block_Product_List) {
                return;
            }
            
            $collection = $block->getLoadedProductCollection();
            $collection->load();
            
            if (count($collection) == 1) {
                $product = $collection->getFirstItem();
                $response = Mage::app()->getResponse();
                $response->setRedirect($product->getProductUrl(false));
                $response->sendResponse();
                exit;
            }
        }
        
        $response = Mage::app()->getResponse();
        $response->setHeader('Expires', gmdate("D, d M Y H:i:s", time() + 600), true);
        $response->setHeader('Cache-Control', 'public, max-age=600, must-revalidate', true);
        $response->setHeader('Pragma', null, true);
    }

}