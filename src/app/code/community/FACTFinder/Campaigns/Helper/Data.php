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
 * Class FACTFinder_Campaigns_Helper_Data
 *
 * @category Mage
 * @package FACTFinder_Campaigns
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015, Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Campaigns_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CATALOG_NAVIGATION_REPLACED_CONFIG_PATH = 'factfinder/modules/catalog_navigation';
    const CAMPAIGNS_IDENTIFIER_CONFIG_PATH = 'factfinder/config/campaigns_identifier';
    const ENABLE_CAMPAIGNS_ON_PROD_PAGE_CONFIG_PATH = 'factfinder/config/enable_campaigns_on_prod_page';


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


}