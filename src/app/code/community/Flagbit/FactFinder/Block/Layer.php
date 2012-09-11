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
 * This class is used to disable Magento´s default Price and Category Filter Output  
 * 
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_Layer extends Flagbit_FactFinder_Block_Layer_Abstract
{

    /**
     * Prepare child blocks
     *
     * @return Mage_Catalog_Block_Layer_View
     */
    protected function _prepareLayout()
    {
        if(Mage::helper('factfinder/search')->getIsEnabled(false, 'campaign')){
            // handle redirects
            $redirect = Mage::getSingleton('factfinder/adapter')->getRedirect();
            if($redirect){
                Mage::app()->getResponse()->setRedirect($redirect);
            }
        }

        if(!Mage::helper('factfinder/search')->getIsEnabled(false, 'asn')){
            return parent::_prepareLayout();
        }

        // set default sort Order
        if(Mage::getSingleton('catalog/session')->getSortOrder()){
            Mage::getSingleton('catalog/session')->setSortOrder('relevance');
        }
        
        $stateBlock = $this->getLayout()->createBlock('catalog/layer_state')
            ->setLayer($this->getLayer());

        $this->setChild('layer_state', $stateBlock);

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $filterBlockName = $this->_getAttributeFilterBlockName();

            $filterBlock = $this->getLayout()->createBlock($filterBlockName)
                    ->setLayer($this->getLayer())
                    ->setAttributeModel($attribute)
                    ->init();

            switch($attribute->getType()){
                              
                case 'slider':
                    if(!($this->getLayout()->getBlock('ffslider') instanceof  Flagbit_FactFinder_Block_Filter_Slider)){
                        $this->getLayout()->getBlock('head')->setChild('ffslider', $this->getLayout()->createBlock('factfinder/filter_slider'));
                    }
                    $filterBlock->setTemplate('factfinder/filter/slider.phtml');
                    $filterBlock->setData((current($attribute->getItems())));
                    $filterBlock->setUnit($attribute->getUnit());
                    break;
            }
            
            $this->setChild($attribute->getAttributeCode().'_filter', $filterBlock);
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
        if(!Mage::helper('factfinder/search')->getIsEnabled(false, 'asn')){
            return parent::_getCategoryFilter();
        }        
        return false;
    }

    /**
     * Retrieve Price Filter block
     *
     * @return Mage_Catalog_Block_Layer_Filter_Price
     */
    protected function _getPriceFilter()
    {
        if(!Mage::helper('factfinder/search')->getIsEnabled(false, 'asn')){
            return parent::_getPriceFilter();
        }        
        
        return false;
    }
    
    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        if (!Mage::helper('factfinder/search')->getIsEnabled(false)
            || (!Mage::helper('factfinder/search')->getIsOnSearchPage() && !Mage::helper('factfinder/search')->getIsEnabled(false, 'asn')) ){
            return parent::canShowBlock();
        }
        if(!Mage::helper('factfinder/search')->getIsEnabled(false, 'asn')){
            return false;
        } 
        if ($this->getLayer()->getFilterableAttributes()->count()
            && $this->getLayer()->getProductCollection()->getSize()) {
            return true;
        }
        return false;
    }    
}
