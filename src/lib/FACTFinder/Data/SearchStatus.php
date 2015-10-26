<?php
namespace FACTFinder\Data;

/**
 * Enum for status of the search result.
 * @see FilterStyle for documentation of the enum workaround.
 */
class SearchStatus
{
    static private $noQuery;
    static private $noResult;
    static private $emptyResult;
    static private $recordsFound;

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
            self::$noQuery      = new SearchStatus();
            self::$noResult     = new SearchStatus();
            self::$emptyResult  = new SearchStatus();
            self::$recordsFound = new SearchStatus();

            self::$initialized = true;
        }
    }

    static public function NoQuery()      { return self::$noQuery; }
    static public function NoResult()     { return self::$noResult; }
    static public function EmptyResult()  { return self::$emptyResult; }
    static public function RecordsFound() { return self::$recordsFound; }
}

SearchStatus::initialize();
