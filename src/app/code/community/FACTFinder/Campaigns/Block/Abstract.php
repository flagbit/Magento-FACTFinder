<?php
class FACTFinder_Campaigns_Block_Abstract extends Mage_Core_Block_Template
{

    /**
     * Render html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('factfinder')->isEnabled('campaigns')) {
            return '';
        }

        return parent::_toHtml();
    }

}