<?php
/**
 * FACTFinder_Asn
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Asn_Model_Observer
 *
 * @category Mage
 * @package FACTFinder_Asn
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Asn_Model_Observer
{


    /**
     * Add layerd navigation filters on current layer block
     *
     * @param Mage_Catalog_Block_Layer_View $block
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
     * @param Varian_Object $observer
     *
     * @return void
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
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function addLayerFiltersToSearch($observer)
    {
        $block = $observer->getBlock();

        if (!$block instanceof Mage_CatalogSearch_Block_Layer
            || !Mage::helper('factfinder')->isEnabled('asn')
        ) {
            return;
        }

        $this->_addLayeredNavigation($block);
    }


    /**
     * Reset current search layer for further use in the block
     *
     * @param Varien_Object $observer
     *
     * @return void
     */
    public function resetCurrentCatalogLayer($observer)
    {
        if (!Mage::helper('factfinder')->isEnabled('catalog_navigation')) {
            return;
        }

        Mage::register('current_layer', Mage::getSingleton('factfinder_asn/catalog_layer'));
    }


}