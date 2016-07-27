<?php
/**
 * Abstract.php
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2016 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link http://www.flagbit.de
 */
abstract class FACTFinder_Tracking_Block_Abstract extends Mage_Core_Block_Template
{


    /**
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected abstract function getProductResultCollection();


    /**
     * Get Product URL to ID Mapping JSON Object
     *
     * @return string
     */
    public function getJsonUrlToIdMappingObject()
    {
        $data = array();
        foreach($this->getProductResultCollection() as $product){
            $data[$product->getProductUrl()] = $product->getId();
        }

        return Mage::helper('core')->jsonEncode($data);
    }


    /**
     * Get Product and Search Details by ID as JSON Object
     *
     * @return string
     */
    public function getJsonDataObject()
    {
        $dataTemplate = $this->getDataTemplate();

        $data = array();
        foreach($this->getProductResultCollection() as $product){
            $data[$product->getId()] = $this->getProductData($product) + $dataTemplate;
        }

        return Mage::helper('core')->jsonEncode($data);
    }


    /**
     * Get product specific data array
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected abstract function getProductData($product);


    /**
     * Get common data for all products
     *
     * @return array
     */
    protected abstract function getDataTemplate();


}