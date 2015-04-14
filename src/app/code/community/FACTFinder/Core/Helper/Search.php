<?php
class FACTFinder_Core_Helper_Search extends Mage_Core_Helper_Abstract
{
    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_PRODUCT_IDENTIFIER = 'factfinder/config/identifier';

    /**
     * Retrieve query model object
     *
     * @return String
     */
    public function getQueryText()
    {
        return Mage::helper('catalogsearch')->getQueryText();
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
            $limit = 2*3*4*5*6; //a lot of products working for each layout
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
            $idFieldName = $this->getEntity()->getIdFieldName();
        }
        return $idFieldName;
    }

}