<?php
namespace FACTFinder\Data;

/**
 * Enum for sorting directions.
 * @see FilterStyle for documentation of the enum workaround.
 */
class SortingDirection
{
    // These will store distinct instances of the class.
    static private $asc;
    static private $desc;

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
            self::$asc         = new SortingDirection();
            self::$desc       = new SortingDirection();

            self::$initialized = true;
        }
    }

    static public function Ascending()     { return self::$asc; }
    static public function Descending()   { return self::$desc; }
}

SortingDirection::initialize();
