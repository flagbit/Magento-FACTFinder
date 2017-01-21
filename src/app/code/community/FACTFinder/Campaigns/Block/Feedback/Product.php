<?php
/**
 * FACTFinder_Campaigns
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */

/**
 * Class FACTFinder_Campaigns_Block_Feedback_Product
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Block_Feedback_Product extends FACTFinder_Campaigns_Block_Feedback_Abstract
{

    protected $_handlerModel = 'factfinder_campaigns/handler_product';


    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getHandler()
    {
        $product = Mage::registry('current_product');
        if ($product) {
            $productId = $product->getData(Mage::helper('factfinder_campaigns')->getIdFieldName());
            return Mage::getSingleton($this->_handlerModel, array($productId));
        }

        return parent::_getHandler();
    }


    /**
     * Check is the campign can be shown on product page
     *
     * @return bool
     */
    protected function _canBeShown()
    {
        if (!Mage::registry('current_product')
            || !Mage::helper('factfinder_campaigns')->canShowCampaignsOnProduct())
        {
            return false;
        }
        return (bool) Mage::helper('factfinder')->isEnabled('campaigns');
    }


}