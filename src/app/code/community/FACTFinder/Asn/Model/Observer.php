<?php

class FACTFinder_Asn_Model_Observer
{

    /**
     * Add factfinder filter block type
     *
     * @param $observer
     */
    public function addLayerFiltersToSearch($observer)
    {
        $block = $observer->getBlock();

        if (!$block instanceof Mage_CatalogSearch_Block_Layer) {
            return;
        }

        $stateBlock = $block->getLayout()->createBlock('catalog/layer_state')
            ->setLayer($block->getLayer());

        $block->setChild('layer_state', $stateBlock);

        $filterableAttributes = Mage::getResourceModel('factfinder_asn/product_attribute_collection');
        foreach ($filterableAttributes as $attribute) {
            $filter = $block->getLayout()
                ->createBlock('factfinder_asn/catalog_layer_factfinder')
                ->setAttributeModel($attribute)
                ->setLayer($block->getLayer())
                ->init();

            $block->setChild($attribute->getAttributeCode() . '_filter', $filter);
        }

        $block->setData('_filterable_attributes', $filterableAttributes);
    }

    /**
     * Reset current search layer for further use in the block
     *
     * @param $observer
     */
    public function resetCurrentCatalogLayer($observer)
    {
        if (Mage::helper('factfinder')->isEnabled()) {
            Mage::register('current_layer', Mage::getSingleton('factfinder_asn/catalog_layer'));
        }
    }
}