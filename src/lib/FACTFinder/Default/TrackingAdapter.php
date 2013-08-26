<?php
/**
 * Tracking adapter using the new tracking API introduced with FF 6.9.
 */
class FACTFinder_Default_TrackingAdapter extends FACTFinder_Abstract_Adapter
{
    // A result (product, banner, ASN element, ...) referenced by the key has been displayed.
    const EVENT_DISPLAY = 'display';
    // Visitor has given feedback about a ResultNode. Reference Key is optional.
    const EVENT_FEEDBACK = 'feedback';
    // The user clicked on a product / detail view.
    const EVENT_INSPECT = 'inspect';
    // The user checked the availability of a product.
    const EVENT_AVAILABILITY_CHECK = 'availabilityCheck';
    // The user added an item to the cart.
    const EVENT_CART = 'cart';
    // The user bought or booked a product or service.
    const EVENT_BUY = 'buy';
    // A request of the user could be answered from the shop cache.
    const EVENT_CACHE_HIT = 'cacheHit';
    // A new session has been created for a user.
    const EVENT_SESSION_START = 'sessionStart';

    /**
     * let the data provider save the tracking params
     *
     * @return boolean $success
     */
    public function applyTracking()
    {
        $this->log->debug("Tracking not available before FF 6.9!");
        return false;
    }

    public function prepareDefaultParams($inputParams, $event) {
        $eventsNoSourceRefKeyRequired = array(
            self::EVENT_INSPECT,
            self::EVENT_CART,
            self::EVENT_BUY,
            self::EVENT_SESSION_START
        );

        $sid = $inputParams['sid'];
        if (strlen($sid) == 0)
            $sid = session_id();

        $sourceRefKey = $inputParams['sourceRefKey'];
        if (!in_array($event, $eventsNoSourceRefKeyRequired, true) && strlen($sourceRefKey) == 0)
            throw new UnexpectedValueException("No sourceRefKey in parameters");

        $params = array('sourceRefKey' => $sourceRefKey, 'sid' => $sid);

        $optParams = array('uid', 'cookieId', 'price', 'amount', 'positive', 'message', 'site', 'id', 'mid');
        foreach ($optParams AS $optParam) {
            if (isset($inputParams[$optParam]) && strlen($inputParams[$optParam]) > 0)
                $params[$optParam] = $inputParams[$optParam];
        }

        return $params;
    }

    public function doTrackingFromRequest()
    {
        $this->setupTrackingFromRequest();
        return $this->applyTracking();
    }

    public function setupTrackingFromRequest()
    {
        $params = $this->getParamsParser()->getServerRequestParams();
        $this->getDataProvider()->setParams($params);
    }

    public function trackEvent($event, $inputParams)
    {
        $this->setupEventTracking($event, $inputParams);
        return $this->applyTracking();
    }

    public function setupEventTracking($event, $inputParams)
    {
        $params = $this->prepareDefaultParams($inputParams, $event);

        $events = array(
            self::EVENT_DISPLAY,
            self::EVENT_FEEDBACK,
            self::EVENT_INSPECT,
            self::EVENT_AVAILABILITY_CHECK,
            self::EVENT_CART,
            self::EVENT_BUY,
            self::EVENT_CACHE_HIT,
            self::EVENT_SESSION_START
        );

        if (!in_array($event, $events, true)) {
            throw new UnexpectedValueException("Event $event not known.");
        }

        $params['event'] = $event;

        $this->getDataProvider()->setParams($params);
    }
}
