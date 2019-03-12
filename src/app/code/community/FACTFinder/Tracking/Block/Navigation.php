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
     * @return FACTFinder_Core_Model_Resource_Search_Collection
     */
    protected function getProductResultCollection()
    {
        /** @var Mage_Catalog_Model_Category $_current_category */
        $_current_category = Mage::registry('current_category');
        $_product_collection = $_current_category->getProductCollection();
        return $_product_collection;
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