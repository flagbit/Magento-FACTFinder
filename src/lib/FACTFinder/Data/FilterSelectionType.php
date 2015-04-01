<?php
namespace FACTFinder\Data;

/**
 * Enum for selection types of filter groups within the After Search Navigation (ASN).
 * @see FilterStyle for documentation of the enum workaround.
 */
class FilterSelectionType
{
    // These will store distinct instances of the class.
    static private $singleHideUnselected;
    static private $singleShowUnselected;
    static private $multiSelectOr;
    static private $multiSelectAnd;

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
            self::$singleHideUnselected = new FilterSelectionType();
            self::$singleShowUnselected = new FilterSelectionType();
            self::$multiSelectOr        = new FilterSelectionType();
            self::$multiSelectAnd       = new FilterSelectionType();

            self::$initialized = true;
        }
    }

    static public function SingleHideUnselected()     { return self::$singleHideUnselected; }
    static public function SingleShowUnselected()     { return self::$singleShowUnselected; }
    static public function MultiSelectOr()            { return self::$multiSelectOr; }
    static public function MultiSelectAnd()           { return self::$multiSelectAnd; }
}

FilterSelectionType::initialize();
