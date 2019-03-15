<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 *
 */

/**
 * Block class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Block_Navigation extends FACTFinder_Tracking_Block_Abstract
{
    const EVENT_NAME = 'click';


    /**
     * Get Product Result Collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getProductResultCollection()
    {
        return Mage::getSingleton('factfinder/catalogSearch_layer')->getProductCollection();
    }

    /**
     * Get Product URL to ID Mapping JSON Object
     *
     * @return string
     */
    public function getJsonUrlToIdMappingObject()
    {

        $_current_category = Mage::registry('current_category');

        //collect urls with category path
        $product_id_to_url = array();
        foreach ($_current_category->getProductCollection() as $product) {
            $product_id_to_url[$product->getId()] = $product->getProductUrl();
        }

        $data = array();
        foreach ($this->getProductResultCollection() as $product) {
            //canonical product url
            $data[$product->getProductUrl()] = $product->getId();
            //product url including category path
            $category_category_url = $product_id_to_url[$product->getId()];
            $data[$category_category_url] = $product->getId();
        }

        return Mage::helper('core')->jsonEncode($data);
    }

    /**
     * Get product specific data array
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return mixed
     */
    protected function getProductData($product)
    {
        $trackingIdFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();
        $masterIdFieldName = Mage::helper('factfinder/search')->getIdFieldName();

        /** @var Mage_Catalog_Model_Category $_current_category */
        $_current_category = Mage::registry('current_category');

        $query = Mage::helper('factfinder_tracking')->getCategoryTrackingPath($_current_category);

        $data = array(
            'id' => $product->getData($trackingIdFieldName),
            'masterId' => $product->getData($masterIdFieldName),
            'pos' => $product->getPosition(),
            'origPos' => $product->getOriginalPosition() ? $product->getOriginalPosition() : $product->getPosition(),
            'title' => $product->getName(),
            'query' => $query
        );

        if ($product->getCampaign() !== null) {
            $data['campaign'] = $product->getCampaign();
        }

        if ($product->getInstoreAds() !== null) {
            $data['instoreAds'] = $product->getInstoreAds();
        }

        return $data;

    }

    /**
     * Get common data for all products
     *
     * @return array
     */
    protected function getDataTemplate()
    {
        $searchHelper = Mage::helper('factfinder/search');

        $dataTemplate = array(
            'page' => $searchHelper->getCurrentPage(),
            'origPageSize' => $searchHelper->getDefaultPerPageValue(),
            'channel' => Mage::getStoreConfig('factfinder/search/channel'),
            'event' => self::EVENT_NAME
        );

        return $dataTemplate;
    }
}