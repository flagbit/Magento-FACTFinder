<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Block class
 * 
 * This class is used to disable Magento´ default Price and Category Filter Output  
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id: AsnFilterItem.php 25893 2010-06-29 08:19:43Z rb $
 */
class Flagbit_FactFinder_Block_Layer extends Mage_CatalogSearch_Block_Layer
{

    /**
     * Prepare child blocks
     *
     * @return Mage_Catalog_Block_Layer_View
     */
    protected function _prepareLayout()
    {
        $stateBlock = $this->getLayout()->createBlock('catalog/layer_state')
            ->setLayer($this->getLayer());

        $this->setChild('layer_state', $stateBlock);


        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $filterBlockName = $this->_getAttributeFilterBlockName();

            $this->setChild($attribute->getAttributeCode().'_filter',
                $this->getLayout()->createBlock($filterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($attribute)
                    ->init());
        }

        $this->getLayer()->apply();
        return Mage_Core_Block_Template::_prepareLayout();
    }	
	
    /**
     * Get category filter block
     *
     * @return Mage_Catalog_Block_Layer_Filter_Category
     */
    protected function _getCategoryFilter()
    {
        return false;
    }

    /**
     * Retrieve Price Filter block
     *
     * @return Mage_Catalog_Block_Layer_Filter_Price
     */
    protected function _getPriceFilter()
    {
        return false;
    }
}
