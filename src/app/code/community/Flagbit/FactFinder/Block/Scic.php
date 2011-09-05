<?php
/**
 * Flagbit_FactFinder
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 */

/**
 * Block class
 *
 * This class is used to disable Magento´s default Price and Category Filter Output
 *
 * @category  Mage
 * @package   Flagbit_FactFinder
 * @copyright Copyright (c) 2010 Flagbit GmbH & Co. KG (http://www.flagbit.de/)
 * @author    Joerg Weller <weller@flagbit.de>
 * @version   $Id$
 */
class Flagbit_FactFinder_Block_Scic extends Mage_Core_Block_Template
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

		$dataTemplate = array(
			'query'			=> $searchHelper->getQuery()->getQueryText(),
			'page'			=> $searchHelper->getCurrentPage(),
			'sid'			=> md5(Mage::getSingleton('core/session')->getSessionId()),
			'pageSize'		=> $searchHelper->getPageLimit(),
			'origPageSize'	=> $searchHelper->getDefaultPerPageValue(),
			'channel'		=> Mage::getStoreConfig('factfinder/search/channel'),
			'event'			=> 'click'
		);

		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		if ($customerId) {
			$dataTemplate['userId'] = md5('customer_' . $customerId);
		}

		$data = array();
		foreach($this->_getProductResultCollection() as $product){
			$key = $product->getId();
			$data[$key] = array(
			    'id'		=> $product->getData($idFieldName),
			    'pos'		=> $product->getPosition(),
			    'origPos'	=> $product->getOriginalPosition(),
			    'title'		=> $product->getName(),
				'simi'		=> $product->getSimilarity()
			);
			$data[$key] += $dataTemplate;
		}

		return Mage::helper('core')->jsonEncode($data);
	}

}