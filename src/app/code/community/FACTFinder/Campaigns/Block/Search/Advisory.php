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
 * Class FACTFinder_Campaigns_Block_Search_Advisory
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Block_Search_Advisory extends FACTFinder_Campaigns_Block_Abstract
{
    /**
     * Search handler
     *
     * @var FACTFinder_Campaigns_Model_Handler_Search
     */
    protected $_searchHandler;


    /**
     * Preparing global layout. Here we initialize the handler
     *
     * @return FACTFinder_Campaigns_Block_Search_Advisory
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('factfinder')->isEnabled('campaigns')) {
            return parent::_prepareLayout();
        }

        $this->_searchHandler = Mage::getSingleton('factfinder_campaigns/handler_search');
    }


    /**
     * Get Campaign Text
     *
     * @return array
     */
    public function getActiveQuestions()
    {
        $questions = array();

        $_campaigns = $this->_searchHandler->getCampaigns();
        if ($_campaigns && $_campaigns->hasActiveQuestions()) {
            $questions = $_campaigns->getActiveQuestions();
        }

        return $questions;
    }


}
