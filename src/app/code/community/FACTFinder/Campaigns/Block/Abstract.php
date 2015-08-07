<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Block_Abstract
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
abstract class FACTFinder_Campaigns_Block_Abstract extends Mage_Core_Block_Template
{

    /**
     * Handler used to get data from ff
     *
     * @var FACTFinder_Campaigns_Model_Handler_Cart
     */
    protected $_handlerModel = '';


    /**
     * Check if campaigns can be shown
     *
     * @return bool
     */
    protected function _canBeShown()
    {
        return (bool) Mage::helper('factfinder')->isEnabled('campaigns');
    }


    /**
     * Preparing global layout
     *
     * @return FACTFinder_Campaigns_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if (Mage::helper('factfinder')->isEnabled('campaigns')) {
            $this->_handler = Mage::getSingleton($this->_handlerModel);
        }

        return parent::_prepareLayout();
    }


    /**
     * Get handler singleton
     *
     * @return \Mage_Core_Model_Abstract
     */
    protected function _getHandler()
    {
        return Mage::getSingleton($this->_handlerModel);
    }


    /**
     * Render html
     * Return empty string if module isn't enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_canBeShown()) {
            return '';
        }

        return parent::_toHtml();
    }


}