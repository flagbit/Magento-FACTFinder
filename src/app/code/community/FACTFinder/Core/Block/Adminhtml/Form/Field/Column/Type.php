<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
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
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Block_Adminhtml_Form_Field_Column_Type extends Mage_Core_Block_Html_Select
{

    /**
     * Set initial options
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('type_select')
            ->setTitle('type')
            ->setClass('type_select')
        ;
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
            foreach (array('text' => 'Text', 'number' => 'Number') as $id => $label) {
                $htmlLabel = htmlspecialchars($label, ENT_QUOTES);
                $this->addOption($id, $htmlLabel);
            }
        }

        return parent::_toHtml();
    }


}
