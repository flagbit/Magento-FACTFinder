<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2013 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Tracking block class
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2013 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <joerg.weller@flagbit.de>
 * @author    Nicolai Essig <nicolai.essig@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_Tracking extends Mage_Core_Block_Template
{

	/**
	 * get Product Result Collection
	 *
	 * @return Flagbit_FactFinder_Model_Mysql4_Search_Collection
	 */
	protected function _getProductResultCollection()
	{
		return Mage::getSingleton('factfinder/layer')->getProductCollection();
	}

	/**
	 * get Product URL to ID Mapping JSON Object
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
	 * get Product and Search Details by ID as JSON Object
	 *
	 * @return string
	 */
	public function getJsonDataObject()
	{
		$searchHelper = Mage::helper('factfinder/search');
		$idFieldName = $searchHelper->getIdFieldName();

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if ($customerId) {
            $customerId = md5('customer_' . $customerId);
        }

        if(Mage::helper('factfinder')->useOldTracking() || Mage::helper('factfinder')->useLegacyTracking())
        {
            $dataTemplate = array(
                'query'         => $searchHelper->getQuery()->getQueryText(),
                'page'          => $searchHelper->getCurrentPage(),
                'sid'           => md5(Mage::getSingleton('core/session')->getSessionId()),
                'pageSize'      => $searchHelper->getPageLimit(),
                'origPageSize'  => $searchHelper->getDefaultPerPageValue(),
                'channel'       => Mage::getStoreConfig('factfinder/search/channel'),
                'userId'        => $customerId,
                'event'         => 'click'
            );
        } else {
            $dataTemplate = array(
                'sourceRefKey'  => Mage::getSingleton('core/session')->getFactFinderRefKey(),
                'sid'           => md5(Mage::getSingleton('core/session')->getSessionId()),
                'uid'           => $customerId,
                'site'          => Mage::app()->getStore()->getCode(),
                'event'         => FACTFinder_Default_TrackingAdapter::EVENT_INSPECT
            );
        }

		$data = array();
		foreach($this->_getProductResultCollection() as $product){
			$key = $product->getId();

            $data[$key] = array(
                'id' => $product->getData($idFieldName),
            );

            if(Mage::helper('factfinder')->useOldTracking())
            {
                $data[$key] += array(
                    'pos'		=> $product->getPosition(),
                    'origPos'	=> $product->getOriginalPosition(),
                    'title'		=> $product->getName(),
                    'simi'		=> $product->getSimilarity()
                );
            }

			$data[$key] += $dataTemplate;
		}

		return Mage::helper('core')->jsonEncode($data);
	}

}