<?php
namespace FACTFinder\Data;

/**
 * Enum for filter types of groups within the After Search Navigation (ASN).
 * @see FilterStyle for documentation of the enum workaround.
 */
class FilterType
{
    // These will store distinct instances of the class.
    static private $text;
    static private $number;

    static private $nextID = 0;
    private $id;
    private function __construct()
    {
        $this->id = self::$nextID++;
    }

    static private $initialized = false;
    static public function initialize()
    {
        if (!self::$initialized)
        {
            self::$text         = new FilterType();
            self::$number       = new FilterType();

            self::$initialized = true;
        }
    }

    static public function Text()     { return self::$text; }
    static public function Number()   { return self::$number; }
}

FilterType::initialize();
