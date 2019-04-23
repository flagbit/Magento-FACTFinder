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
 * Helper class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Helper_Data extends Mage_Core_Helper_Abstract
{

     /**
     * Get the correct path where the tracking should be sent
     *
     * @return string
     */
    public function getTrackingUrlPath()
    {
        return 'ff_tracking/proxy/tracking';
    }

    /**
     * Get session id which was initialy saved at session start
     *
     * @return string
     */
    public function getSessionId()
    {
        return md5(Mage::getSingleton('customer/session')->getData('ff_session_id'));
    }


    /**
     * Get id field name for tracking and recommendation.
     *
     * @return bool
     */
    public function getIdFieldName()
    {
        return Mage::getStoreConfig('factfinder/config/tracking_identifier');
    }


    /**
     * @param $category Mage_Catalog_Model_Category
     * @param int|null $storeId
     * @return null|string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getCategoryTrackingPath($category, $storeId = null)
    {
        $categoryNames = array();

        $storeId = $this->getStoreIdOrDefault($storeId);

        $parentCategories = $category->getParentCategories();

        if (is_array($parentCategories) && count($parentCategories) > 0) {
            foreach ($parentCategories as $parentCategory) {

                if ($storeId == Mage::app()->getStore()->getId()) {
                    $categoryInDefaultStore = $parentCategory;
                } else {
                    $categoryInDefaultStore = Mage::getModel('catalog/category')
                        ->setStoreId($storeId)
                        ->load($parentCategory->getId());
                }

                if ($categoryInDefaultStore instanceof Mage_Catalog_Model_Category) {
                    array_push($categoryNames, $categoryInDefaultStore->getName());
                }
            }

            $path = implode($categoryNames, '/');

            return $path;
        }

        return null;
    }

    /**
     * @param $storeId int|null
     * @return int
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function getStoreIdOrDefault($storeId)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();

            if (is_null($storeId)) {
                $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
            }
        }
        return $storeId;
    }
}
