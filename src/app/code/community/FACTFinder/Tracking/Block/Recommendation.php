<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
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
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Block_Recommendation extends FACTFinder_Tracking_Block_Abstract
{
    const EVENT_NAME = 'recommendationClick';


    /**
     * Get Product Result Collection
     *
     * @return FACTFinder_Core_Model_Resource_Search_Collection
     */
    protected function getProductResultCollection()
    {
        return Mage::registry('recommendation_collection');
    }


    /**
     * Get product specific data array
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function getProductData($product)
    {
        $idFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();

        $data =  array(
            'id'       => $product->getData($idFieldName),
            'masterId' => $product->getData($idFieldName),
        );

        return $data;
    }


    /**
     * Get common data for all products
     *
     * @return array
     */
    protected function getDataTemplate()
    {
        $mainProduct = Mage::registry('current_product');
        if (!$mainProduct) {
            return array();
        }

        $idFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();

        $dataTemplate = array(
            'mainId' => $mainProduct->getData($idFieldName),
            'event'  => self::EVENT_NAME
        );

        return $dataTemplate;
    }


}
