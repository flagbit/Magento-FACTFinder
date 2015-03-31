<?php
class FACTFinder_Core_Model_Observer
{
    const SEARCH_ENGINE         = 'factfinder_core/search_engine';
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
}