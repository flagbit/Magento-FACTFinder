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
 * Adminhtml system comfiguration attributes renderer
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Block_Adminhtml_Form_Field_Attributes
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    const RENDERER_ATTRIBUTE = 'factfinder/adminhtml_form_field_column_attribute';
    const RENDERER_TYPE = 'factfinder/adminhtml_form_field_column_type';
    const RENDERER_UNIT = 'factfinder/adminhtml_form_field_column_unit';

    protected $_columnRenderers = array();

    protected $_template = 'factfinder/core/system/config/form/field/attributes.phtml';


    public function __construct()
    {
        if (!$this->_addButtonLabel) {
            $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add');
        }
        parent::__construct();
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
            'renderer' => $this->_getRenderer(self::RENDERER_ATTRIBUTE),
        ));

        $this->addColumn('type', array(
            'label' => Mage::helper('factfinder')->__('Data Type'),
            'renderer' => $this->_getRenderer(self::RENDERER_TYPE),
        ));

        $this->addColumn('unit', array(
            'label' => Mage::helper('factfinder')->__('Unit'),
            'renderer' => $this->_getRenderer(self::RENDERER_UNIT),
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
            'option_extra_attr_' . $this->_getRenderer(self::RENDERER_TYPE)
                ->calcOptionHash($row->getData('type')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer(self::RENDERER_ATTRIBUTE)
                ->calcOptionHash($row->getData('attribute')),
            'selected="selected"'
        );
    }


    /**
     * @param       $blockType
     * @param array $data
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _getRenderer($blockType, $data = array())
    {
        if (!isset($this->_columnRenderers[$blockType])) {
            $renderer = $this->getLayout()->createBlock(
                $blockType, '',
                array('is_render_to_js_template' => true)
            );

            foreach ($data as $key => $value) {
                $renderer->setData($key, $value);
            }

            $this->_columnRenderers[$blockType] = $renderer;
        }

        return $this->_columnRenderers[$blockType];
    }


    protected function _toHtml()
    {
        $html = parent::_toHtml();

        return $this->_afterToHtml($html);
    }


}

