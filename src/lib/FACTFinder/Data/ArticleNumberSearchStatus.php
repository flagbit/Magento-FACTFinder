<?php
namespace FACTFinder\Data;

/**
 * Enum for article number status of the search result.
 * @see FilterStyle for documentation of the enum workaround.
 */
class ArticleNumberSearchStatus
{
    static private $isArticleNumberResultFound;
    static private $isNoArticleNumberResultFound;
    static private $isNoArticleNumberSearch;

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
            self::$isArticleNumberResultFound      = new ArticleNumberSearchStatus();
            self::$isNoArticleNumberResultFound     = new ArticleNumberSearchStatus();
            self::$isNoArticleNumberSearch  = new ArticleNumberSearchStatus();

            self::$initialized = true;
        }
    }

    static public function IsArticleNumberResultFound()      { return self::$isArticleNumberResultFound; }
    static public function IsNoArticleNumberResultFound()     { return self::$isNoArticleNumberResultFound; }
    static public function IsNoArticleNumberSearch()  { return self::$isNoArticleNumberSearch; }
}

ArticleNumberSearchStatus::initialize();
