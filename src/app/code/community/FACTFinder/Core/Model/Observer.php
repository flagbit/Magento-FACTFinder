<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Model_Observer
{
    const SEARCH_ENGINE = 'factfinder/search_engine';
    const DEFAULT_SEARCH_ENGINE = 'catalogsearch/fulltext_engine';
    const REDIRECT_ALREADY_CHECKED_FLAG = 'factifinder_single_result_redirect_flag';


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

        if (empty($groups['search']['fields']['enabled']['value'])) {
            Mage::app()->getConfig()->saveConfig('catalog/search/engine', self::DEFAULT_SEARCH_ENGINE);
            return;
        }

        Mage::app()->getConfig()->saveConfig('catalog/search/engine', self::SEARCH_ENGINE);

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
    }


    /**
     * Manage modules availability
     * Enable them only if they were enable in core configuration
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function manageModules($observer)
    {
        $config = Mage::getConfig();
        $modules = $config->getNode('modules');

        $mustCleanCache = false;
        foreach ($modules->children() as $module => $data) {
            if (strpos($module, 'FACTFinder_') === 0 && $module !== 'FACTFinder_Core') {
                $configName = strtolower(str_replace('FACTFinder_', '', $module));
                $isActivated = Mage::helper('factfinder')->isModuleActivated($configName);

                $currentState = (string) $config->getNode("modules/{$module}/active");
                if ((bool) $currentState === (bool) $isActivated) {
                    continue;
                }

                Mage::helper('factfinder')->updateModuleState($module, $isActivated);
                $mustCleanCache = true;
            }
        }

        if ($mustCleanCache) {
            Mage::app()->cleanCache();
        }
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
        if (Mage::registry(self::REDIRECT_ALREADY_CHECKED_FLAG)
            || Mage::app()->getRequest()->getParam('p', 0) > 1
        ) {
            return;
        }

        Mage::register(self::REDIRECT_ALREADY_CHECKED_FLAG, true, true);

        $helper = Mage::helper('factfinder');
        if ($helper->isRedirectForSingleResult()) {
            $block = Mage::app()->getLayout()->getBlock('search_result_list');

            if (!$block instanceof Mage_Catalog_Block_Product_List) {
                return;
            }

            $collection = $block->getLoadedProductCollection();
            $collection->load();

            if (count($collection) === 1) {
                Mage::dispatchEvent('factfinder_redirect_on_single_result_before',
                    array('product' => $collection->getFirstItem())
                );
                $helper->redirectToProductPage($collection->getFirstItem());
            }
        }
    }


}