<?php
/**
 * FACTFinder_Core
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Helper class
 *
 * @category Mage
 * @package FACTFinder_Core
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG (http://www.flagbit.de)
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Core_Helper_Search extends Mage_Core_Helper_Abstract
{
    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_PRODUCT_IDENTIFIER = 'factfinder/config/identifier';

    const REQUEST_ID_PREFIX = 'FACTFINDER_';

    const DEFAULT_ENTITY_ID_FIELD_NAME = 'entity_id';


    /**
     * Retrieve query model object
     *
     * @return String
     */
    public function getQueryText()
    {
        return strip_tags(Mage::helper('catalogsearch')->getQueryText());
    }


    /**
     * get Page Limit
     *
     * @return int
     */
    public function getPageLimit()
    {
        $limit = $this->_getToolbarBlock()->getLimit();
        if ($limit == 'all') {
            $limit = 720; // number of products to fit for each layout: 2 * 3 * 4 * 5 * 6 per row
        }

        return $limit;
    }


    /**
     * get Toolbar Block
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    protected function _getToolbarBlock()
    {
        $mainBlock = Mage::app()->getLayout()->getBlock('search.result');
        if ($mainBlock instanceof Mage_CatalogSearch_Block_Result) {
            $toolbarBlock = $mainBlock->getListBlock()->getToolbarBlock();
        } else {
            $toolbarBlock = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar');
        }

        return $toolbarBlock;
    }


    /**
     * get current Page Number
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_getToolbarBlock()->getCurrentPage();
    }


    /**
     * get Entity ID Field Name by Configuration or via Entity
     *
     * @return string
     */
    public function getIdFieldName()
    {
        $idFieldName = Mage::getStoreConfig(self::XML_CONFIG_PATH_PRODUCT_IDENTIFIER);
        if (!$idFieldName) {
            $idFieldName = self::DEFAULT_ENTITY_ID_FIELD_NAME;
        }

        return $idFieldName;
    }


    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        return $this->_getToolbarBlock()->getDefaultPerPageValue();
    }


    /**
     * get current Order
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        return $this->_getToolbarBlock()->getCurrentOrder();
    }


    /**
     * get current Order Direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        return $this->_getToolbarBlock()->getCurrentDirection();
    }


    /**
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getQuery()
    {
        return Mage::helper('catalogsearch')->getQuery();
    }


    /**
     * Get Module Status depending on Module
     *
     * @return bool
     */
    public function getIsOnSearchPage()
    {
        $moduleName = Mage::app()->getRequest()->getModuleName();
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $actionName = Mage::app()->getRequest()->getActionName();

        if (($moduleName == 'catalogsearch' && $controllerName === 'result')
            || ($moduleName == 'xmlconnect' && strpos($actionName, 'search') !== false)
        ) {
            return true;
        }

        return false;
    }


    /**
     * Check if redirect to product page for single result
     *
     * @return bool
     */
    public function isRedirectForSingleResult()
    {
        return (bool) Mage::app()->getStore()->getConfig('factfinder/config/redirectOnSingleResult');
    }


    /**
     * Check if we should use sorting items delivered from FF
     *
     * @return bool
     */
    public function useSortings()
    {
        return (bool) Mage::app()->getStore()->getConfig('factfinder/config/use_sortings');
    }

    /**
     * Check if we should use results per page items delivered from FF
     *
     * @return bool
     */
    public function useResultsPerPageOptions()
    {
        return (bool) Mage::app()->getStore()->getConfig('factfinder/config/use_resultsPerPage');
    }

    /**
     * Redirect to product page
     *
     * @param \Mage_Catalog_Model_Product $product
     */
    public function redirectToProductPage(Mage_Catalog_Model_Product $product)
    {
        $response = Mage::app()->getResponse();
        $response->setRedirect($product->getProductUrl());
        $response->sendResponse();
        exit;
    }


}
