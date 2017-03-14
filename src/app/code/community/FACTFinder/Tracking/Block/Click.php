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
class FACTFinder_Tracking_Block_Click extends FACTFinder_Tracking_Block_Abstract
{
    const EVENT_NAME = 'click';


    /**
     * Get Product Result Collection
     *
     * @return FACTFinder_Core_Model_Resource_Search_Collection
     */
    protected function getProductResultCollection()
    {
        return Mage::getSingleton('factfinder/catalogSearch_layer')->getProductCollection();
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
        $idFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();

        $data =  array(
            'id'       => $product->getData($idFieldName),
            'masterId' => $product->getData($idFieldName),
            'pos'      => $product->getPosition(),
            'origPos'  => $product->getOriginalPosition() ? $product->getOriginalPosition() : $product->getPosition(),
            'title'    => $product->getName()
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
            'query'        => $searchHelper->getQuery()->getQueryText(),
            'page'         => $searchHelper->getCurrentPage(),
            'origPageSize' => $searchHelper->getDefaultPerPageValue(),
            'channel'      => Mage::getStoreConfig('factfinder/search/channel'),
            'event'        => self::EVENT_NAME
        );

        return $dataTemplate;
    }


}
