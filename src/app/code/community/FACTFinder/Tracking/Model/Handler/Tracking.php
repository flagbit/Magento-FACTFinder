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
 * Model class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2015 Flagbit GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Model_Handler_Tracking extends FACTFinder_Core_Model_Handler_Abstract
{
    protected $_facadeModel = 'factfinder_tracking/facade';

    /**
     * Just a stub
     */
    protected function _configureFacade()
    {
    }


    /**
     * Track a product which was added to the cart.
     *
     * @param string $id id of product
     * @param string $masterId masterId of product if variant
     * @param string $title title of product (optional - is empty by default)
     * @param string $query query which led to the product (only if module Semantic Enhancer is used)
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $cookieId cookie id (optional)
     * @param int $count number of items purchased for each product (optional - default 1)
     * @param float $price this is the single unit price (optional)
     * @param string $userid id of user (optional if modul personalisation is not used)
     *
     * @return boolean
     */
    public function trackCart(
        $id,
        $masterId = null,
        $title = '',
        $query = null,
        $sid = null,
        $cookieId = null,
        $count = 1,
        $price = null,
        $userid = null
    )
    {
        // Call old scic tracking
        if (Mage::helper('factfinder_tracking')->useOldTracking()) {
            return $this->_getFacade()->getScicAdapter()->trackCart(
                $id,
                $sid,
                $count,
                $price,
                $userid
            );
        }

        // Call new tracking
        return $this->_getFacade()->getTrackingAdapter()->trackCart(
            $id,
            $masterId,
            $title,
            $query,
            $sid,
            $cookieId,
            $count,
            $price,
            $userid
        );
    }


    /**
     * Track a detail click on a product.
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $query query which led to the product
     * @param int $pos position of product in the search result
     * @param int $origPos original position of product in the search result. this data is delivered by ff (optional)
     * @param int $page page number where the product was clicked (optional - is 1 by default)
     * @param float $simi similiarity of the product (optional - is 100.00 by default)
     * @param string $title title of product (optional - is empty by default)
     * @param int $pageSize size of the page where the product was found (optional - is 12 by default)
     * @param int $origPageSize original size of the page before the user could have changed it (optional)
     *
     * @return bool
     */
    public function trackClick(
        $id,
        $query,
        $pos,
        $masterId = null,
        $sid = null,
        $cookieId = null,
        $origPos = -1,
        $page = 1,
        $simi = 100.0,
        $title = '',
        $pageSize = 12,
        $origPageSize = -1,
        $userid = null
    )
    {
        // Call old scic tracking
        if (Mage::helper('factfinder_tracking')->useOldTracking()) {
            return $this->_getFacade()->getScicAdapter()->trackClick(
                $id,
                $sid,
                $query,
                $pos,
                $origPos,
                $page,
                $simi,
                $title,
                $pageSize,
                $origPageSize
            );
        }

        // Call new tracking
        return $this->_getFacade()->getTrackingAdapter()->trackClick(
            $id,
            $query,
            $pos,
            $masterId,
            $sid,
            $cookieId,
            $origPos,
            $page,
            $simi,
            $title,
            $pageSize,
            $origPageSize,
            $userid
        );
    }


    /**
     *
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     *
     * @param string $id
     * @param string $masterId
     * @param string $title
     * @param string $query
     * @param int $sid
     * @param string $cookieId
     * @param int $count
     * @param float $price
     * @param int $userid
     *
     * @return mixed
     */
    public function setupCheckoutTracking(
        $id,
        $masterId = null,
        $title = '',
        $query = null,
        $sid = null,
        $cookieId = null,
        $count = 1,
        $price = null,
        $userid = null
    )
    {
        // Call old scic tracking
        if (Mage::helper('factfinder_tracking')->useOldTracking()) {
            return $this->_getFacade()->getScicAdapter($id)->setupCheckoutTracking(
                $id,
                $sid,
                $count,
                $price,
                $userid
            );
        }

        // Call new tracking
        return $this->_getFacade()->getTrackingAdapter($id)->setupCheckoutTracking(
            $id,
            $masterId,
            $title,
            $query,
            $sid,
            $cookieId,
            $count,
            $price,
            $userid
        );
    }


    /**
     * Send tracking
     *
     * @param $instance
     *
     * @return bool
     */
    public function applyTracking($instance = null)
    {
        // Call old scic tracking
        if (Mage::helper('factfinder_tracking')->useOldTracking()) {
            return $this->_getFacade()->getScicAdapter($instance)->applyTracking();
        }

        // Call new tracking
        return $this->_getFacade()->getTrackingAdapter($instance)->applyTracking();
    }


}
