<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Adminhtml system comfiguration attributes renderer
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Block_Adminhtml_Form_Field_Attributes
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    /**
     * @var FACTFinder_Core_Block_Adminhtml_Form_Field_Attribute
     */
    protected $_attributeRenderer;


    /**
     * Retrieve group column renderer
     *
     * @return FACTFinder_Core_Block_Adminhtml_Form_Field_Attribute
     */
    protected function _getAttributeRenderer()
    {
        if (!$this->_attributeRenderer) {
            $this->_attributeRenderer = $this->getLayout()->createBlock(
                'factfinder/adminhtml_form_field_attribute', '',
                array('is_render_to_js_template' => true)
            );
            $this->_attributeRenderer->setClass('attribute_select');
            $this->_attributeRenderer->setExtraParams('style="width:200px"');
        }

        return $this->_attributeRenderer;
    }


    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('attribute', array(
            'label' => Mage::helper('factfinder')->__('Attribute'),
            'renderer' => $this->_getAttributeRenderer(),
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('factfinder')->__('Add Attribute');
    }


    /**
     * Prepare existing row data object
     *
     * @param Varien_Object $row
     *
     * @return void
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getAttributeRenderer()->calcOptionHash($row->getData('attribute')),
            'selected="selected"'
        );
    }


}

