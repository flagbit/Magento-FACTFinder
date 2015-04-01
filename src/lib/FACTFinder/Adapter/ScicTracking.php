<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

/**
 * This tracking adapter uses the old SCIC (Shopping Cart Information Collector)
 * interface, which is recommended over the new Analytic Dashboard tracking
 * until further notice.
 */
class ScicTracking extends AbstractAdapter
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
     * @return bool Success?
     */
    public function doTrackingFromRequest($sid = null)
    {
        $this->setupTrackingFromRequest($sid);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupTrackingFromRequest($sid = null)
    {
        if (strlen($sid) > 0)
            $this->parameters['sid'] = $sid;
        else if (!isset($this->parameters['sid'])
                 || strlen($this->parameters['sid']) == 0
        ) {
            $this->parameters['sid'] = session_id();
        }
    }

    /**
     * Track a detail click on a product.
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param string $query query which led to the product
     * @param int $pos position of product in the search result
     * @param int $origPos original position of product in the search result. this data is delivered by FACT-Finder (optional - is set equals to $position by default)
     * @param int $page page number where the product was clicked (optional - is 1 by default)
     * @param float $simi similiarity of the product (optional - is 100.00 by default)
     * @param string $title title of product (optional - is empty by default)
     * @param int $pageSize size of the page where the product was found (optional - is 12 by default)
     * @param int $origPageSize original size of the page before the user could have changed it (optional - is set equals to $page by default)
     * @return boolean $success
     */
    public function trackClick(
        $id,
        $sid = null,
        $query,
        $pos,
        $origPos = -1,
        $page = 1,
        $simi = 100.0,
        $title = '',
        $pageSize = 12,
        $origPageSize = -1
    ) {
        $this->setupClickTracking($id, $sid, $query, $pos, $origPos, $page,
                                  $simi, $title, $pageSize, $origPageSize);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupClickTracking(
        $id,
        $sid = null,
        $query,
        $pos,
        $origPos = -1,
        $page = 1,
        $simi = 100.0,
        $title = '',
        $pageSize = 12,
        $origPageSize = -1
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        if ($origPos == -1) $origPos = $pos;
        if ($origPageSize == -1) $origPageSize = $pageSize;

        $this->parameters->clear();
        $this->parameters->setAll(
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
     * Track a product which was added to the cart.
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param int $count number of items purchased for each product (optional - default 1)
     * @param float $price this is the single unit price (optional)
     * @return boolean $success
     */
    public function trackCart(
        $id,
        $sid = null,
        $count = 1,
        $price = null,
        $userid = null
    ) {
        $this->setupCartTracking($id, $sid, $count, $price, $userid);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupCartTracking(
        $id,
        $sid = null,
        $count = 1,
        $price = null,
        $userid = null
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id' => $id,
            'sid' => $sid,
            'count' => $count,
            'event' => 'cart'
        );

        if (strlen($price) > 0) $params['price'] = $price;
        if (strlen($userid) > 0) $params['userid'] = $userid;

        $this->parameters->clear();
        $this->parameters->setAll($params);
    }

    /**
     * Track a product which was purchased.
     *
     * @param string $id id of product
     * @param string $sid session id (if empty, then try to set using the function session_id() )
     * @param int $count number of items purchased for each product (optional - default 1)
     * @param float $price this is the single unit price (optional)
     * @param string $userid id of user checking out
     * @return boolean $success
     */
    public function trackCheckout(
        $id,
        $sid = null,
        $count = 1,
        $price = null,
        $userid = null
    ) {
        $this->setupCheckoutTracking($id, $sid, $count, $price, $userid);
        return $this->applyTracking();
    }

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupCheckoutTracking(
        $id,
        $sid = null,
        $count = 1,
        $price = null,
        $userid = null
    ) {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id' => $id,
            'sid' => $sid,
            'count' => $count,
            'event' => 'checkout'
        );

        if (strlen($price) > 0) $params['price'] = $price;
        if (strlen($userid) > 0) $params['userid'] = $userid;

        $this->parameters->clear();
        $this->parameters->setAll($params);
    }

    /**
     * Track a click on a recommended product.
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

    /**
     * Use this method directly if you want to separate the setup from sending
     * the request. This is particularly useful when using the
     * MultiCurlRequestFactory.
     */
    public function setupRecommendationClickTracking($id, $sid = null, $mainId)
    {
        if (strlen($sid) == 0) $sid = session_id();
        $params = array(
            'id' => $id,
            'sid' => $sid,
            'mainId' => $mainId,
            'event' => 'recommendationClick'
        );

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
