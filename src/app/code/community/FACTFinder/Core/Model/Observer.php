<?php
class FACTFinder_Core_Model_Observer
{
    const SEARCH_ENGINE         = 'factfinder/search_engine';
    const DEFAULT_SEARCH_ENGINE = 'catalogsearch/fulltext_engine';


    /**
     * Replace the config entry with search engine when enabling the module
     *
     * @param $observer
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

        // todo check if it can be enabled

        Mage::app()->getConfig()->saveConfig('catalog/search/engine', self::SEARCH_ENGINE);
    }


    /**
     * Reset current search layer for further use in the block
     *
     * @param $observer
     */
    public function resetCurrentSearchLayer($observer)
    {
        if (Mage::helper('factfinder')->isEnabled()) {
            Mage::register('current_layer', Mage::getSingleton('factfinder/catalogSearch_layer'));
        }
    }


    /**
     * Remove all layered navigation filters on search page
     *
     * @param $observer
     */
    public function removeLayerFilters($observer)
    {
        $block = $observer->getBlock();

        if (!$block instanceof Mage_CatalogSearch_Block_Layer) {
            return;
        }

        $block->unsetChildren();
        $block->setData('_filterable_attributes', array());
    }
}