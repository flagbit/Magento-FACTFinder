<?php
namespace FACTFinder\Data;

/**
 * Enum for type of a tracking event.
 * @see FilterStyle for documentation of the enum workaround.
 */
class TrackingEventType
{
    static private $display;
    static private $feedback;
    static private $inspect;
    static private $availabilityCheck;
    static private $cart;
    static private $buy;
    static private $cacheHit;
    static private $sessionStart;

    static private $nextID = 0;
    private $id;
    private function __construct()
    {
        $this->id = self::$nextID++;
    }

    static private $initialized = false;
    public static function initialize()
    {
        if (!self::$initialized)
        {
            self::$display           = new TrackingEventType();
            self::$feedback          = new TrackingEventType();
            self::$inspect           = new TrackingEventType();
            self::$availabilityCheck = new TrackingEventType();
            self::$cart              = new TrackingEventType();
            self::$buy               = new TrackingEventType();
            self::$cacheHit          = new TrackingEventType();
            self::$sessionStart      = new TrackingEventType();

            self::$initialized = true;
        }
    }

    // A result (search result or suggest) referenced by the key has been displayed.
    static public function Display()           { return self::$display; }
    // The user has given geedback about a result. Reference key is optional.
    static public function Feedback()          { return self::$feedback; }
    // The user viewed a product / detail view by clicking or hovering.
    static public function Inspect()           { return self::$inspect; }
    // The user checked the availability of a product.
    static public function AvailabilityCheck() { return self::$availabilityCheck; }
    // The user added an item to the cart.
    static public function Cart()              { return self::$cart; }
    // The user bought or booked a product or service.
    static public function Buy()               { return self::$buy; }
    // A request of the user could be answered from the client cache.
    static public function CacheHit()          { return self::$cacheHit; }
    // A new session has been created for a user.
    static public function SessionStart()      { return self::$sessionStart; }
}

TrackingEventType::initialize();
