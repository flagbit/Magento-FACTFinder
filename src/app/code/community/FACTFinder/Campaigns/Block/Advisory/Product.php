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
 * Class FACTFinder_Campaigns_Block_Advisory_Product
 *
 * Provides advisory hints to the product view page
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Block_Advisory_Product extends FACTFinder_Campaigns_Block_Abstract
{

    /**
     * @var string
     */
    protected $_handlerModel = 'factfinder_campaigns/handler_product';


    /**
     * @return \Mage_Core_Model_Abstract
     */
    protected function _getHandler()
    {
        return Mage::getSingleton('factfinder_campaigns/handler_product', $this->_getProductIds());
    }


    /**
     * @return array
     */
    protected function _getProductIds()
    {
        $productIds = array();
        if (Mage::registry('current_product')) {
            $productIds = array(
                Mage::registry('current_product')->getData(Mage::helper('factfinder/search')->getIdFieldName())
            );
        }

        return $productIds;
    }


    /**
    * Get campaign questions and answers
    *
    * @return array
    */
    public function getActiveQuestions()
    {
        $questions = array();

        if ($this->canCampaignBeDisplay()) {
            $questions = $this->_getHandler()->getActiveAdvisorQuestions();
        }

        return $questions;
    }


    /**
     * Check if campaign can be displayed
     *
     * @return bool
     */
    protected function canCampaignBeDisplay()
    {
        return (bool) Mage::registry('current_product');
    }


}