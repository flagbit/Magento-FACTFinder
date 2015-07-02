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
 * Class FACTFinder_Campaigns_Block_Search_Feedback
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Block_Search_Feedback extends FACTFinder_Campaigns_Block_Abstract
{
    /**
     * Pushed Products Collection
     *
     * @var FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    protected $_pushedProductsCollection = null;


    /**
     * Search handler. Used on search and category pages
     *
     * @var FACTFinder_Campaigns_Model_Handler_Search
     */
    protected $_searchHandler;


    /**
     * Preparing global layout. Here we initialize the handler
     *
     * @return FACTFinder_Campaigns_Block_Search_Feedback
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('factfinder')->isEnabled('campaigns')) {
            return parent::_prepareLayout();
        }

        $this->_searchHandler = Mage::getSingleton('factfinder_campaigns/handler_search');

        return parent::_prepareLayout();
    }


    /**
     * Get Campaign Text
     *
     * @return string
     */
    public function getText()
    {
        $text = '';

        $_campaigns = $this->_searchHandler->getCampaigns();
        if ($_campaigns && $_campaigns->hasFeedback() && $this->getTextNumber()) {
            $text = $_campaigns->getFeedback($this->getTextNumber() - 1);
        }

        return $text;
    }


    /**
     * Pushed Products Collection
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function getPushedProductsCollection()
    {
        if ($this->_pushedProductsCollection === null) {
            $this->_pushedProductsCollection = Mage::getResourceModel('factfinder_campaigns/pushedproducts_collection');
        }

        return $this->_pushedProductsCollection;
    }


}