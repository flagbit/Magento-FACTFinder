<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

/**
 * This tracking adapter uses the new Tracking Interface for 6.11
 */
class Tracking extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($loggerClass, $configuration, $request,
                            $urlBuilder, $encodingConverter);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->request->setAction('Tracking.ff');

        $this->request->setConnectTimeout($configuration->getTrackingConnectTimeout());
        $this->request->setTimeout($configuration->getTrackingTimeout());

        // $this->usePassthroughResponseContentProcessor(); (default)
    }

    /**
     * If all needed parameters are available at the request like described in
     * the documentation, just use this method to fetch the needed parameters
     * and track them. Make sure to set a session id if there is no parameter
     * "sid". If this argument is not set or empty and the parameter "sid" is
     * not available, it will try to use session_id() to fetch one.
     *
     * @param string $sid session id
     * @param string $userId id of user
     * @return bool Success?
     */
    public function doTrackingFromRequest($sid = null, $userId = null)
    {
        $this->setupTrackingFromRequest($sid, $userId);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     *
     * @param string $sid session id
     * @param string $userId id of user
     */
    public function setupTrackingFromRequest($sid = null, $userId = null)
    {
        if (strlen($sid) > 0)
            $this->parameters['sid'] = $sid;
        else if (!isset($this->parameters['sid'])
                 || strlen($this->parameters['sid']) == 0
        ) {
            $this->parameters['sid'] = session_id();
        }

        if (strlen($userId) > 0)
            $this->parameters['userId'] = $userId;
    }

    /**
     * Track a detail click on a product.
     *
     * @param string $id tracking id of product (see field with the role "Product number for tracking")
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $query query which led to the product
     * @param int $pos position of product in the search result
     * @param string $masterId master id of the product (see field with the role "Master article number")
     * @param string $cookieId cookie id (optional)
     * @param int $origPos original position of product in the search result. this data is delivered by FACT-Finder (optional - is set equals to $position by default)
     * @param int $page page number where the product was clicked (optional - is 1 by default)
     * @param float $simi similiarity of the product (optional - is 100.00 by default)
     * @param string $title title of product (optional - is empty by default)
     * @param int $pageSize size of the page where the product was found (optional - is 12 by default)
     * @param int $origPageSize original size of the page before the user could have changed it (optional - is set equals to $page by default)
     * @param string $userId id of user (optional if modul personalisation is not used)
     * @param string $campaign campaign name (optional)
     * @param boolean $instoreAds determines whether it's a sponsered product (optional)
     * @return boolean $success
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
        $userId = null,
        $campaign = null,
        $instoreAds = false
    ) {
        $this->setupClickTracking($id, $query, $pos, $masterId, $sid, $cookieId, $origPos, $page,
                                  $simi, $title, $pageSize, $origPageSize, $userId, $campaign, $instoreAds);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupClickTracking(
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
        $userId = null,
        $campaign = null,
        $instoreAds = false
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        if ($origPos == -1) $origPos = $pos;
        if ($origPageSize == -1) $origPageSize = $pageSize;
        $params = array(
            'id'            => $id,
            'query'         => $query,
            'pos'           => $pos,
            'sid'           => $sid,
            'origPos'       => $origPos,
            'page'          => $page,
            'simi'          => $simi,
            'title'         => $title,
            'event'         => 'click',
            'pageSize'      => $pageSize,
            'origPageSize'  => $origPageSize,
        );
        
        if (strlen($userId) > 0) $params['userId'] = $userId;
        if (strlen($cookieId) > 0) $params['cookieId'] = $cookieId;
        if (strlen($masterId) > 0) $params['masterId'] = $masterId;
        if (strlen($campaign) > 0) $params['campaign'] = $campaign;
        if ($instoreAds) $params['instoreAds'] = 'true';
        
        $this->parameters->clear();
        $this->parameters->setAll($params);
    }

    /**
     * Track a product which was added to the cart.
     *
     * @param string $id tracking id of product (see field with the role "Product number for tracking")
     * @param string $masterId master id of the product (see field with the role "Master article number")
     * @param string $tile title of product (optional - is empty by default)
     * @param string $query query which led to the product (only if module Semantic Enhancer is used)
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $cookieId cookie id (optional)
     * @param int $count number of items purchased for each product (optional - default 1)
     * @param float $price this is the single unit price (optional)
     * @param string $userId id of user (optional if modul personalisation is not used)
     * @param string $campaign campaign name (optional)
     * @param boolean $instoreAds determines whether it's a sponsered product (optional)
     * @return boolean $success
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
        $userId = null,
        $campaign = null,
        $instoreAds = false
    ) {
        $this->setupCartTracking($id, $masterId, $title, $query, $sid, $cookieId, $count, $price, $userId, $campaign, $instoreAds);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupCartTracking(
        $id,
        $masterId = null,
        $title = '',
        $query = null,
        $sid = null,
        $cookieId = null,
        $count = 1,
        $price = null,
        $userId = null,
        $campaign = null,
        $instoreAds = false
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id'        => $id,
            'title'     => $title,
            'sid'       => $sid,
            'count'     => $count,
            'event'     => 'cart'
        );

        if (strlen($price) > 0) $params['price'] = $price;
        if (strlen($userId) > 0) $params['userId'] = $userId;
        if (strlen($cookieId) > 0) $params['cookieId'] = $cookieId;
        if (strlen($masterId) > 0) $params['masterId'] = $masterId;
        if (strlen($query) > 0) $params['query'] = $query;
        if (strlen($campaign) > 0) $params['campaign'] = $campaign;
        if ($instoreAds) $params['instoreAds'] = 'true';
        
        $this->parameters->clear();
        $this->parameters->setAll($params);
    }

    /**
     * Track a product which was purchased.
     *
     * @param string $id tracking id of product (see field with the role "Product number for tracking")
     * @param string $masterId master id of the product (see field with the role "Master article number")
     * @param string $tile title of product (optional - is empty by default)
     * @param string $query query which led to the product (only if module Semantic Enhancer is used)
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $cookieId cookie id (optional)
     * @param int $count number of items purchased for each product (optional - default 1)
     * @param float $price this is the single unit price (optional)
     * @param string $userId id of user (optional if modul personalisation is not used)
     * @param string $campaign campaign name (optional)
     * @param boolean $instoreAds determines whether it's a sponsered product (optional)
     * @return boolean $success
     */
    public function trackCheckout(
        $id,
        $masterId = null,
        $title = '',
        $query = null,
        $sid = null,
        $cookieId = null,
        $count = 1,
        $price = null,
        $userId = null,
        $campaign = null,
        $instoreAds = false
    ) {
        $this->setupCheckoutTracking($id, $masterId, $title, $query, $sid, $cookieId, $count, $price, $userId, $campaign, $instoreAds);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
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
        $userId = null,
        $campaign = null,
        $instoreAds = false
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id'        => $id,
            'masterId'  => $masterId,
            'title'     => $title,
            'sid'       => $sid,
            'count'     => $count,
            'event'     => 'checkout'
        );

        if (strlen($price) > 0) $params['price'] = $price;
        if (strlen($userId) > 0) $params['userId'] = $userId;
        if (strlen($cookieId) > 0) $params['cookieId'] = $cookieId;
        if (strlen($query) > 0) $params['query'] = $query;
        if (strlen($masterId) > 0) $params['masterId'] = $masterId;
        if (strlen($campaign) > 0) $params['campaign'] = $campaign;
        if ($instoreAds) $params['instoreAds'] = 'true';
        
        $this->parameters->clear();
        $this->parameters->setAll($params);
    }

    /**
     * Track a click on a recommended product.
     *
     * @param string $id tracking id of product (see field with the role "Product number for tracking")
     * @param int $mainId ID of the product for which the clicked-upon item was recommended
     * @param string $masterId master id of the product (see field with the role "Master article number")
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $cookieId cookie id (optional)
     * @param string $userId id of user (optional if modul personalisation is not used)
     * @return boolean $success
     */
    public function trackRecommendationClick(
        $id,
        $mainId,
        $masterId = null,
        $sid = null,
        $cookieId = null,
        $userId = null
    ) {
        $this->setupRecommendationClickTracking($id, $mainId, $masterId, $sid, $cookieId, $userId);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupRecommendationClickTracking(
        $id,
        $mainId,
        $masterId = null,
        $sid = null,
        $cookieId = null,
        $userId = null
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id'        => $id,
            'masterId'  => $masterId,
            'sid'       => $sid,
            'cookieId'  => $cookieId,
            'mainId'    => $mainId,
            'event'     => 'recommendationClick'
        );
        
        if (strlen($userId) > 0) $params['userId'] = $userId;
        if (strlen($cookieId) > 0) $params['cookieId'] = $cookieId;
        if (strlen($masterId) > 0) $params['masterId'] = $masterId;
        
        $this->parameters->clear();
        $this->parameters->setAll($params);
    }
    
    /**
     * Track login of a user
     *
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $cookieId cookie id (optional)
     * @param string $userId id of user who logged in
     * @return boolean $success
     */
    public function trackLogin(
        $sid = null,
        $cookieId = null,
        $userId = null
    ) {
        $this->setupLoginTracking($sid, $cookieId, $userId);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupLoginTracking(
        $sid = null,
        $cookieId = null,
        $userId = null
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'sid'       => $sid,
            'userId'    => $userId,
            'event'     => 'login'
        );
        
        if (strlen($cookieId) > 0) $params['cookieId'] = $cookieId;
        
        $this->parameters->clear();
        $this->parameters->setAll($params);
    }

    /**
     * send tracking
     *
     * @return boolean $success
     */
    public function applyTracking() {
        $success = trim($this->getResponseContent());
        return $success == 'The event was successfully tracked' || $success == 'true' || $success == '1';
    }
}
