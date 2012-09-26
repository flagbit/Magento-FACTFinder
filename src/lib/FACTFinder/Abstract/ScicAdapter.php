<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Abstract
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * abstract adapter for the shopping cart information collector tracking
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: ScicAdapter.php 25896 2010-06-29 08:34:06Z rb $
 * @package   FACTFinder\Abstract
 */
abstract class FACTFinder_Abstract_ScicAdapter extends FACTFinder_Abstract_Adapter
{
    /**
     * let the data provider save the tracking params
     *
     * @return boolean $success
     */
    abstract public function applyTracking();

    /**
     * if all needed parameters are available at the request like described in the documentation, just use this method to
     * fetch the needed parameters and track them
     * insure to set a session id if there is no parameter "sid". if this argument is not set or empty and the parameter
     * "sid" is not available, it will try to use session_id() to fetch one.
     *
     * @param string $sid session id
     * @return boolean $success
     */
    public function doTrackingFromRequest($sid = null)
    {
        $this->setupTrackingFromRequest($sid);
        return $this->applyTracking();
    }

    public function setupTrackingFromRequest($sid = null)
    {
        $params = $this->getParamsParser()->getServerRequestParams();
        if (strlen($sid) > 0) {
            $params['sid'] = $sid;
        } else if (!isset($params['sid']) || strlen($params['sid']) == 0) {
            $params['sid'] = session_id();
        }
        $this->getDataProvider()->setParams($params);
    }

    /**
     * track a detail click on a product
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $query query which led to the product
     * @param int $pos position of product in the search result
     * @param int $origPos original position of product in the search result. this data is delivered by FACT-Finder (optional - is set equals to $position by default)
     * @param int $page page number where the product was clicked (optional - is 1 by default)
     * @param double $simi similiarity of the product (optional - is 100.00 by default)
     * @param string $title title of product (optional - is empty by default)
     * @param int $pageSize size of the page where the product was found (optional - is 12 by default)
     * @param int $origPageSize original size of the page before the user could have changed it (optional - is set equals to $page by default)
     * @return boolean $success
     */
    public function trackClick($id, $sid = null, $query, $pos, $origPos = -1, $page = 1, $simi = 100.0, $title = '',
                               $pageSize = 12, $origPageSize = -1)
    {
        $this->setupClickTracking($id, $sid, $query, $pos, $origPos, $page, $simi, $title, $pageSize, $origPageSize);
        return $this->applyTracking();
    }

    public function setupClickTracking($id, $sid = null, $query, $pos, $origPos = -1, $page = 1, $simi = 100.0,
                                       $title = '', $pageSize = 12, $origPageSize = -1)
    {
        if (strlen($sid) == 0) $sid = session_id();
        if ($origPos == -1) $origPos = $pos;
        if ($origPageSize == -1) $origPageSize = $pageSize;

        $this->getDataProvider()->setParams(
            array(
                'query' => $query,
                'id' => $id,
                'pos' => $pos,
                'origPos' => $origPos,
                'page' => $page,
                'simi' => $simi,
                'sid' => $sid,
                'title' => $title,
                'event' => 'click',
                'pageSize' => $pageSize,
                'origPageSize' => $origPageSize
            )
        );
    }

    /**
     * track a product which was added to the cart
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param int $count number of items purchased for each product (optional - default 1)
     * @param double $price this is the single unit price (optional)
     * @return boolean $success
     */
    public function trackCart($id, $sid = null, $count = 1, $price = null, $userid = null)
    {
        $this->setupCartTracking($id, $sid, $count, $price, $userid);
        return $this->applyTracking();
    }

    public function setupCartTracking($id, $sid = null, $count = 1, $price = null, $userid = null)
    {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id' => $id,
            'sid' => $sid,
            'count' => $count,
            'event' => 'cart'
        );

        if (strlen($price) > 0) $params['price'] = $price;
        if (strlen($userid) > 0) $params['userid'] = $userid;

        $this->getDataProvider()->setParams($params);
    }

    /**
     * track a product which was purchased
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param int $count number of items purchased for each product (optional - default 1)
     * @param double $price this is the single unit price (optional)
     * @return boolean $success
     */
    public function trackCheckout($id, $sid = null, $count = 1, $price = null, $userid = null)
    {
        $this->setupCheckoutTracking($id, $sid, $count, $price, $userid);
        return $this->applyTracking();
    }

    public function setupCheckoutTracking($id, $sid = null, $count = 1, $price = null, $userid = null)
    {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id' => $id,
            'sid' => $sid,
            'count' => $count,
            'event' => 'checkout'
        );

        if (strlen($price) > 0) $params['price'] = $price;
        if (strlen($userid) > 0) $params['userid'] = $userid;

        $this->getDataProvider()->setParams($params);
    }

    /**
     * track a click on a recommended product
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param int $mainId ID of the product for which the clicked-upon item was recommended
     * @return boolean $success
     */
    public function trackRecommendationClick($id, $sid = null, $mainId)
    {
        $this->setupRecommendationClickTracking($id, $sid, $mainId);
        return $this->applyTracking();
    }

    public function setupRecommendationClickTracking($id, $sid = null, $mainId)
    {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id' => $id,
            'sid' => $sid,
            'mainId' => $mainId,
            'event' => 'recommendationClick'
        );
        $this->getDataProvider()->setParams($params);
    }
}