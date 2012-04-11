<?php

class Flagbit_FactFinder_Block_Adminhtml_Form_Field_Attribute extends Mage_Core_Block_Html_Select
{
    /**
     * Storeviews cache
     *
     * @var array
     */
    private $_attributes;

    /**
     * Retrieve allowed Storeviews
     *
     * @param int $storeId  return name by storeview id
     * @return array|string
     */
    protected function _getAttributes($storeId = null)
    {
        if (is_null($this->_attributes)) {
            $this->_attributes = array();
            $collection = Mage::getModel('eav/entity_attribute')->getCollection();
            $collection->setEntityTypeFilter(Mage::getSingleton('eav/config')->getEntityType('catalog_product'));
            foreach ($collection as $item) {
                /* @var $item Mage_Core_Model_Store */
                $this->_attributes[$item->getAttributeCode()] = $item->getFrontendLabel().' ('.$item->getAttributeCode().')';
            }
        }
        if (!is_null($storeId)) {
            return isset($this->_attributes[$storeId]) ? $this->_attributes[$storeId] : null;
        }
        return $this->_attributes;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getAttributes() as $id => $label) {
                $this->addOption($id, addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}
