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
class FACTFinder_Core_Block_Adminhtml_Form_Field_Column_Unit extends Mage_Core_Block_Abstract
{

    const COLUMN_NAME = 'unit';


    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'id'    => 'attribute_unit',
            'name'  => self::COLUMN_NAME,
            'title' => $this->__('Attribute Unit'),
            'class' => 'attribute_unit',
        ));
    }


    /**
     * Render HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        $html = '<input name="' . $this->getName() . '" id="' . $this->getId() . '" class="'
            . $this->getClass() . '" title="' . $this->getTitle() . '" ' . $this->getExtraParams()
            . 'value="#{' . self::COLUMN_NAME . '}">';

        $html .= '</input>';

        return $html;
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


}
