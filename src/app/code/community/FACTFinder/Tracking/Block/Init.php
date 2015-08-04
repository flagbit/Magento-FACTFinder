<?php
/**
 * FACTFinder_Tracking
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 *
 */

/**
 * Block class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Block_Init extends Mage_Core_Block_Template
{


    /**
     * Get Product Result Collection
     *
     * @return FACTFinder_Core_Model_Resource_Search_Collection
     */
    protected function _getProductResultCollection()
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
        $data = array();
        foreach($this->_getProductResultCollection() as $product){
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
        $searchHelper = Mage::helper('factfinder/search');
        $idFieldName = Mage::helper('factfinder_tracking')->getIdFieldName();

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if ($customerId) {
            $customerId = md5('customer_' . $customerId);
        }

        $dataTemplate = array(
            'query'         => $searchHelper->getQuery()->getQueryText(),
            'page'          => $searchHelper->getCurrentPage(),
            'sid'           => Mage::helper('factfinder_tracking')->getSessionId(),
            'pageSize'      => $searchHelper->getPageLimit(),
            'origPageSize'  => $searchHelper->getDefaultPerPageValue(),
            'channel'       => Mage::getStoreConfig('factfinder/search/channel'),
            'userId'        => $customerId,
            'event'         => 'click'
        );

        $data = array();
        foreach($this->_getProductResultCollection() as $product){
            $key = $product->getId();

            $data[$key] = array(
                'id' => $product->getData($idFieldName),
                'masterid' => $product->getData($idFieldName),
                'pos'      => $product->getPosition(),
                'origPos'  => $product->getOriginalPosition() ? $product->getOriginalPosition() : $product->getPosition(),
                'title'    => $product->getName(),
                'simi'     => $product->getSimilarity()
            );

            $data[$key] += $dataTemplate;
        }

        return Mage::helper('core')->jsonEncode($data);
    }


}
