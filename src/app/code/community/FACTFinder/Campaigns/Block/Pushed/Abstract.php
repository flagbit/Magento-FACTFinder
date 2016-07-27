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
 * Class FACTFinder_Campaigns_Block_Pushed_Abstract
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
abstract class FACTFinder_Campaigns_Block_Pushed_Abstract extends Mage_Catalog_Block_Product_List_Upsell
{
    const HEADER_LABEL = 'pushed products header';

    protected $_template = 'factfinder/campaigns/pushed.phtml';

    /**
     * Handler model to use
     * Must be redefined in descendants
     *
     * @var string
     */
    protected $_handlerModel = '';


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
     * Prepare collection data
     *
     * @return FACTFinder_Campaigns_Block_Pushed_Abstract
     */
    protected function _prepareData()
    {
        $this->getItemCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('thumbnail')
            ->addPriceData();

        return $this;
    }


    /**
     * Get pushed products collection
     *
     * @return FACTFinder_Campaigns_Model_Resource_Pushedproducts_Collection
     */
    public function getItemCollection()
    {
        if ($this->_itemCollection === null) {
            $this->_itemCollection = Mage::getResourceModel('factfinder_campaigns/pushedproducts_collection');
            $this->_itemCollection->setHandler($this->_getHandler());
        }

        return $this->_itemCollection;
    }


    /**
     * Get feedback header text
     *
     * @return string
     */
    public function getHeader()
    {
        $campaigns = $this->_getHandler()->getCampaigns();

        $label = $campaigns->getFeedback(self::HEADER_LABEL);

        if (!empty($label)) {
            return $label;
        }

        return $this->__('Pushed products');
    }


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