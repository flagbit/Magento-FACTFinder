<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Class FACTFinder_Core_Block_Adminhtml_Form_Field_Attribute
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Block_Adminhtml_Form_Field_Attribute extends Mage_Core_Block_Html_Select
{

    /**
     * Attributes cache
     *
     * @var array
     */
    protected $_attributes = array();


    /**
     * Retrieve attributes array
     *
     * @return array|string
     */
    protected function _getAttributes()
    {
        if (empty($this->_attributes)) {
            $collection = Mage::getModel('eav/entity_attribute')->getCollection();
            $collection->setEntityTypeFilter(Mage::getSingleton('eav/config')->getEntityType('catalog_product'));
            foreach ($collection as $item) {
                $code = $item->getAttributeCode();
                /** @var $item Mage_Core_Model_Store */
                $this->_attributes[$code] = $item->getFrontendLabel().' ('.$code.')';
            }
        }

        return $this->_attributes;
    }


    /**
     * Set name of the html input
     *
     * @param string $value
     *
     * @return mixed
     */
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
                $htmlLabel = htmlspecialchars($label, ENT_QUOTES);
                $this->addOption($id, $htmlLabel);
            }
        }

        return parent::_toHtml();
    }


}
