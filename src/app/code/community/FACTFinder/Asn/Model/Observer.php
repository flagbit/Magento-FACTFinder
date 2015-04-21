<?php

class FACTFinder_Asn_Model_Observer
{

    /**
     * Add layerd navigation filters on current layer block
     *
     * @param $block
     *
     * @return $this
     */
    protected function _addLayeredNavigation($block)
    {
        $stateBlock = $block->getLayout()->createBlock('catalog/layer_state')
            ->setLayer($block->getLayer());

        $block->setChild('layer_state', $stateBlock);

        $filterableAttributes = Mage::getResourceModel('factfinder_asn/product_attribute_collection');
        foreach ($filterableAttributes as $index => $attribute) {
            $filter = $block->getLayout()
                ->createBlock('factfinder_asn/catalog_layer_factfinder')
                ->setAttributeModel($attribute)
                ->setLayer($block->getLayer())
                ->init();

            $block->setChild($attribute->getAttributeCode() . '_filter', $filter);

            // remove category filter - it's enough to add it as a child
            if ($attribute->getAttributeCode() == 'category') {
                $filterableAttributes->removeItemByKey($index);
            }
        }

        $block->setData('_filterable_attributes', $filterableAttributes);

        return $this;
    }


    /**
     * Add factfinder navigation on catalog
     *
     * @param $observer
     */
    public function addLayerFiltersToCatalog($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled('catalog_navigation')) {
            return;
        }

        $block = $observer->getBlock();

        if (!$block instanceof Mage_Catalog_Block_Layer_View
            || $block instanceof Mage_CatalogSearch_Block_Layer
        ) {
            return;
        }

        $this->_addLayeredNavigation($block);
    }


    /**
     * Add factfinder navigation on search page
     *
     * @param $observer
     */
    public function addLayerFiltersToSearch($observer)
    {
        $block = $observer->getBlock();

        if (!$block instanceof Mage_CatalogSearch_Block_Layer) {
            return;
        }

        $this->_addLayeredNavigation($block);
    }

    /**
     * Reset current search layer for further use in the block
     *
     * @param $observer
     */
    public function resetCurrentCatalogLayer($observer)
    {
        Mage::register('current_layer', Mage::getSingleton('factfinder_asn/catalog_layer'));
    }
}