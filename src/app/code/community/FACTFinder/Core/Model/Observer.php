<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Observer class
 *
 * Used to replace search engine, search layer, manage modules availability
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Observer
{
    const SEARCH_ENGINE = 'factfinder/search_engine';
    const DEFAULT_SEARCH_ENGINE = 'catalogsearch/fulltext_engine';
    const REDIRECT_ALREADY_CHECKED_FLAG = 'factifinder_single_result_redirect_flag';
    const SCOPE_STORE = 'stores';
    const SCOPE_WEBSITE = 'websites';


    /**
     * Replace the config entry with search engine when enabling the module
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function setSearchEngine($observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        if ($request->getParam('section') != 'factfinder') {
            return;
        }

        $groups = $request->getPost('groups');
        $website = $request->getParam('website');
        $store   = $request->getParam('store');

        if (is_array($groups['search'])
            && is_array($groups['search']['fields'])
            && is_array($groups['search']['fields']['enabled'])
            && isset($groups['search']['fields']['enabled']['value'])
        ) {
            $value = $groups['search']['fields']['enabled']['value'];
        } elseif ($store) {
            $value = Mage::app()->getWebsite($website)->getConfig('factfinder/search/enabled');
        } else {
            $value = (string) Mage::getConfig()->getNode('default/factfinder/search/enabled');
        }

        if (empty($value)) {
            Mage::app()->getConfig()->saveConfig('catalog/search/engine', self::DEFAULT_SEARCH_ENGINE);
            return;
        }

        $storeIds = $this->getStoreIdsFromRequest($request);
        foreach ($storeIds as $storeId) {
            $errors = Mage::helper('factfinder/backend')->checkConfigData($groups['search']['fields'], $storeId);
            $this->_handleEngine($errors, $this->getScope($request), $this->getScopeId($request));
        }

        // this also helps with module managing
        Mage::app()->cleanCache();
        if (Mage::helper('core')->isModuleEnabled('Enterprise_PageCache')) {
            Enterprise_PageCache_Model_Cache::getCacheInstance()
                ->clean(Enterprise_PageCache_Model_Processor::CACHE_TAG);
        }
    }


    /**
     * Reset current search layer for further use in the block
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function resetCurrentSearchLayer($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled()) {
            return;
        }

        Mage::register('current_layer', Mage::getSingleton('factfinder/catalogSearch_layer'));
    }


    /**
     * Remove all layered navigation filters on search page
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function removeLayerFilters($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled()) {
            return;
        }

        $block = $observer->getBlock();

        if (!$block instanceof Mage_CatalogSearch_Block_Layer) {
            return;
        }

        $block->unsetChildren();
        $block->setData('_filterable_attributes', array());

        if(Mage::helper('core')->isModuleEnabled('Mage_ConfigurableSwatches')) {
            $this->_disableConfigurableSwatchesEvent();
        }
    }


    /**
     * Disable ConfigurableSwatches observer logic
     */
    protected function _disableConfigurableSwatchesEvent()
    {
        // Initialize reflection for Mage::app()->_events
        $mageApp = new ReflectionObject(Mage::app());
        $_events = $mageApp->getProperty('_events');
        $_events->setAccessible(true);

        // Disable ConfigurableSwatch Event
        Mage::getConfig()->getEventConfig('frontend','controller_action_layout_generate_blocks_after')
            ->setNode('/observers/configurableswatches/type', 'disabled');

        $_events->setAccessible(false);
    }


    /**
     * Redirect to product page on single result
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function handleSingleProductRedirect($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled() || !Mage::helper('factfinder/search')->getIsOnSearchPage()) {
            return;
        }

        $block = Mage::app()->getLayout()->getBlock('search_result_list');
        if (!($block instanceof Mage_Catalog_Block_Product_List)) {
            return;
        }

        if (Mage::registry(self::REDIRECT_ALREADY_CHECKED_FLAG)
            || Mage::app()->getRequest()->getParam('p', 0) > 1
        ) {
            return;
        }

        Mage::register(self::REDIRECT_ALREADY_CHECKED_FLAG, true, true);

        if ($this->shouldRedirectToProduct()) {
            $collection = $block->getLoadedProductCollection();
            $product = $collection->getFirstItem();
            Mage::dispatchEvent('factfinder_redirect_on_single_result_before',
                array('product' => $product)
            );

            /** @var FACTFinder_Core_Helper_Search $helper */
            $helper = Mage::helper('factfinder/search');
            $helper->redirectToProductPage($product);
        }
    }


    /**
     * @return bool
     */
    protected function shouldRedirectToProduct()
    {
        /** @var FACTFinder_Core_Helper_Search $helper */
        $helper = Mage::helper('factfinder/search');
        if (!$helper->getIsOnSearchPage() || !$helper->isRedirectForSingleResult()) {
            return false;
        }

        /** @var FACTFinder_Core_Model_Handler_Search $searchHandler */
        $searchHandler = Mage::getSingleton('factfinder/handler_search');

        $articleNumberStatus = $searchHandler->getArticleNumberStatus();

        if ($articleNumberStatus === \FACTFinder\Data\ArticleNumberSearchStatus::IsArticleNumberResultFound()
            && $searchHandler->getSearchResultCount() == 1
        ) {
            return true;
        }

        return false;
    }


    /**
     * Get scope from request
     *
     * @param Varien_Object $request
     *
     * @return string
     */
    protected function getScope($request)
    {
        $website = $request->getParam('website');
        $store = $request->getParam('store');

        return $store ? self::SCOPE_STORE : ($website ? self::SCOPE_WEBSITE : 'default');
    }


    /**
     * Get scope id from request
     *
     * @param Varien_Object $request
     *
     * @return int
     *
     * @throws \Mage_Core_Exception
     */
    protected function getScopeId($request)
    {
        $scope = $this->getScope($request);

        if ($scope === self::SCOPE_WEBSITE) {
            return Mage::app()->getWebsite($request->getParam('website'))->getId();
        } elseif ($scope === self::SCOPE_STORE) {
            return Mage::app()->getStore($request->getParam('store'))->getId();
        }

        return 0;
    }


    /**
     * Save engine configuration
     *
     * @param array  $errors
     * @param string $scope
     * @param int    $scopeId
     *
     * @return $this
     */
    protected function _handleEngine($errors, $scope = 'default', $scopeId = 0)
    {
        if (!empty($errors)) {
            foreach ($errors as $error) {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }

            Mage::app()->getConfig()
                ->saveConfig('catalog/search/engine', self::DEFAULT_SEARCH_ENGINE, $scope, $scopeId)
                ->saveConfig('factfinder/search/enabled', 0, $scope, $scopeId);
        } else {
            Mage::app()->getConfig()
                ->saveConfig('catalog/search/engine', self::SEARCH_ENGINE, $scope, $scopeId);
        }

        return $this;
    }


    /**
     * Get store ids for scope
     *
     * @param Varien_Object $request
     *
     * @return array
     *
     * @throws \Mage_Core_Exception
     */
    protected function getStoreIdsFromRequest($request)
    {
        $scope = $this->getScope($request);
        $scopeId = $this->getScopeId($request);

        if ($scope == self::SCOPE_STORE) {
            return array($scopeId);
        } elseif ($scope == self::SCOPE_WEBSITE) {
            return Mage::app()->getWebsite($scopeId)->getStoreIds();
        }

        return array(0);
    }


}