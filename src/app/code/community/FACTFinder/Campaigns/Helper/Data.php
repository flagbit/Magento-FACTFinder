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
 * Class FACTFinder_Campaigns_Helper_Data
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016, Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CATALOG_NAVIGATION_REPLACED_CONFIG_PATH = 'factfinder/modules/catalog_navigation';
    const CAMPAIGNS_IDENTIFIER_CONFIG_PATH = 'factfinder/config/campaigns_identifier';
    const ENABLE_CAMPAIGNS_ON_PROD_PAGE_CONFIG_PATH = 'factfinder/config/enable_campaigns_on_prod_page';
    const ENABLE_LANDING_PAGE_CAMPAIGNS_CONFIG_PATH = 'factfinder/config/enable_landing_page_campaigns';
    const ENABLE_START_PAGE_CAMPAIGNS_CONFIG_PATH = 'factfinder/config/enable_start_page_campaigns';


    /**
     * Check config if showing campaigns on product page is enabled
     *
     * @return bool
     */
    public function canShowCampaignsOnProduct()
    {
        return (bool) Mage::app()->getStore()->getConfig(self::ENABLE_CAMPAIGNS_ON_PROD_PAGE_CONFIG_PATH);
    }
    
    /**
     * Check config if showing campaigns on product page is enabled
     *
     * @return bool
     */
    public function canShowLandingPageCampaigns()
    {
        return (bool) Mage::app()->getStore()->getConfig(self::ENABLE_LANDING_PAGE_CAMPAIGNS_CONFIG_PATH);
    }

    /**
     * Check config if showing campaigns on start page is enabled
     *
     * @return bool
     */
    public function canShowStartPageCampaigns()
    {
        return (bool) Mage::app()->getStore()->getConfig(self::ENABLE_START_PAGE_CAMPAIGNS_CONFIG_PATH);
    }


    /**
     * Get id field name for campaigns
     *
     * @return bool
     */
    public function getIdFieldName()
    {
        return Mage::getStoreConfig(self::CAMPAIGNS_IDENTIFIER_CONFIG_PATH);
    }


    /**
     * Check is catalog navigation replacement is enabled
     *
     * @return bool
     */
    public function isCatalogNavigationReplaced()
    {
        return (bool) Mage::app()->getStore()->getConfig(self::CATALOG_NAVIGATION_REPLACED_CONFIG_PATH);
    }

    /**
     * Determines whether the current page is a category start page which can access page campaigns.
     *
     * @return bool
     */
    public function getIsOnLandingPage()
    {
        if (!Mage::registry('current_category')
           || Mage::registry('current_category')->getDisplayMode() === Mage_Catalog_Model_Category::DM_PRODUCT 
           || !self::canShowLandingPageCampaigns())
        {
            return false;
        }
        return true;
    }

    /**
     * Determines whether the current page is the start page which can access page campaigns.
     *
     * @return bool
     */
    public function getIsOnStartPage()
    {
        if (Mage::getSingleton('cms/page')->getIdentifier() !== 'home'
           || !self::canShowStartPageCampaigns())
        {
            return false;
        }
        return true;
    }

    /**
     * Determines whether the current page is including a FACT-Finder result listing.
     *
     * @return bool
     */
    public function getIsResultListingPage()
    {
        if (Mage::registry('current_category')
            && !Mage::helper('factfinder_campaigns')->isCatalogNavigationReplaced()
            || Mage::registry('current_product')
        ) {
            return false;
        }
        return true;
    }

}