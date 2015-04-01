<?php
namespace FACTFinder\Data;

/**
 * Enum for type of a bread crumb item.
 * @see FilterStyle for documentation of the enum workaround.
 */
class BreadCrumbType
{
    static private $search;
    static private $filter;

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
            self::$search      = new BreadCrumbType();
            self::$filter      = new BreadCrumbType();

            self::$initialized = true;
        }
    }

    static public function Search()      { return self::$search; }
    static public function Filter()      { return self::$filter; }
}

BreadCrumbType::initialize();
