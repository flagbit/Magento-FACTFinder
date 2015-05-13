<?php

class FACTFinder_Asn_Model_Layer_Filter_Factfinder extends Mage_Catalog_Model_Layer_Filter_Abstract
{
    /**
     * Array of Magento Layer Filter Items
     *
     * @var mixed
     */
    protected $_filterItems = null;

    /**
     * Array of Selected Layer Filters
     *
     * @var mixed
     */
    protected $_selectedFilterItems = array();


    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Varien_Object $filterBlock
     *
     * @return  Mage_Catalog_Model_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $this->_getItemsData();
        $_attributeCode = $filterBlock->getAttributeModel()->getAttributeCode();
        if (isset($this->_selectedFilterItems[$_attributeCode])
            && is_array($this->_selectedFilterItems[$_attributeCode])
        ) {

            foreach ($this->_selectedFilterItems[$_attributeCode] as $optionData) {
                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($optionData)
                );
            }
        }
        return $this;
    }


    /**
     * Create filter item object
     *
     * @param   array $data
     *
     * @return  Mage_Catalog_Model_Layer_Filter_Item
     */
    protected function _createItem($data, $value = '', $count = 0)
    {
        $item = Mage::getModel('factfinder_asn/layer_filter_item')
            ->setFilter($this);

        foreach ($data as $key => $value) {
            $method = 'set' . ucwords($key);
            $item->$method($value);
        }

        return $item;
    }


    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if ($this->_filterItems === null) {
            $attribute = $this->getAttributeModel();
            $this->_requestVar = 'filter' . $attribute->getAttributeCode();

            $options = $attribute->getItems();
            $this->_filterItems = array();
            if (is_array($options)) {
                foreach ($options as $option) {
                    if ($option['selected'] == true) {
                        $this->_selectedFilterItems[$attribute->getAttributeCode()][] = $option;
                        continue;
                    }
                    $this->_filterItems[] = $option;
                }
            }
        }

        return $this->_filterItems;
    }


    /**
     * Initialize filter items
     *
     * @return  Mage_Catalog_Model_Layer_Filter_Abstract
     */
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items = array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItem($itemData);
        }

        $this->_items = $items;

        return $this;
    }
}
