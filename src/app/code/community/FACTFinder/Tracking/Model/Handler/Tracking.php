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
 * Model class
 *
 * @category Mage
 * @package FACTFinder_Tracking
 * @author Flagbit Magento Team <magento@flagbit.de>
 * @copyright Copyright (c) 2017 Flagbit GmbH & Co. KG
 * @license https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link http://www.flagbit.de
 */
class FACTFinder_Tracking_Model_Handler_Tracking extends FACTFinder_Core_Model_Handler_Abstract
{

    protected $_facadeModel = 'factfinder_tracking/facade';


    /**
     * Just a stub
     *
     * @return void
     */
    protected function _configureFacade()
    {
    }


    /**
     * Set store id for facade
     *
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId = 0)
    {
        $this->_getFacade()->setStoreId($storeId);

        return $this;
    }


    /**
     * Track login of a user
     *
     * @param string $sid      session id (if empty, then try to set using the function session_id() )
     * @param string $cookieId cookie id (optional)
     * @param string $userid   id of user who logged in
     *
     * @return boolean $success
     */
    public function trackLogin(
        $sid = null,
        $cookieId = null,
        $userid = null
    ) {
        return $this->_getFacade()->getTrackingAdapter()->trackLogin(
            $sid,
            $cookieId,
            $userid
        );
    }

    /**
     * Track a product which was added to the cart.
     *
     * @param string $id       id of product
     * @param string $masterId masterId of product if variant
     * @param string $title    title of product (optional - is empty by default)
     * @param string $query    query which led to the product (only if module Semantic Enhancer is used)
     * @param string $sid      session id (if empty, then try to set using the function session_id() )
     * @param string $cookieId cookie id (optional)
     * @param int    $count    number of items purchased for each product (optional - default 1)
     * @param float  $price    this is the single unit price (optional)
     * @param string $userid   id of user (optional if modul personalisation is not used)
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
    ) {
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
     * @param string $id           id of product
     * @param string $query        query which led to the product
     * @param int    $pos          position of product in the search result
     * @param mixed  $masterId
     * @param string $sid          session id (if empty, then try to set using the function session_id() )
     * @param mixed  $cookieId
     * @param int    $origPos      original product position in search result. this data is delivered by ff (optional)
     * @param int    $page         page number where the product was clicked (optional - is 1 by default)
     * @param float  $simi         similarity of the product (optional - is 100.00 by default)
     * @param string $title        title of product (optional - is empty by default)
     * @param int    $pageSize     size of the page where the product was found (optional - is 12 by default)
     * @param int    $origPageSize original size of the page before the user could have changed it (optional)
     * @param int    $userid
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
    ) {
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
     * @param int    $sid
     * @param string $cookieId
     * @param int    $count
     * @param float  $price
     * @param int    $userid
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
    ) {
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
     * @param mixed $instance
     *
     * @return bool
     */
    public function applyTracking($instance = null)
    {
        return $this->_getFacade()->getTrackingAdapter($instance)->applyTracking();
    }


    /**
     * Get an instance of FACT-Finder facade
     *
     * @return FACTFinder_Core_Model_Facade
     */
    protected function _getFacade()
    {
        if ($this->_facade === null) {
            // get new model, not singleton
            $this->_facade = Mage::getModel($this->_facadeModel);
        }

        return $this->_facade;
    }


}
