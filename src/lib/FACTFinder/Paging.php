<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * Class for creating pagelinks on a search result page. by iterating over an paging object, you will get FACTFinder_Item
 * objects which represent the link for each page, beginning from "getFirtPageNumberShown()" to "getLastPageNumberShown()".
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Paging.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 **/
class FACTFinder_Paging implements IteratorAggregate
{
    private $iterator;
    private $currentPage;
    private $pageCount;
    protected $paramsParser;
    protected $params;
    protected $displayPageCount = 9;

    /* New in 6.9. Should be version specific, but Paging is outside of the versioning structure atm. */
    protected $sourceRefKey = null;

    /**
     * class constructor - puts paging data from the SimpleXMLElement object
     * into useful structure
     *
     * @param int $currentPage
     * @param int $pageCount
     * @param FACTFinder_ParametersParser $paramsParser because this class is creating its urls
     */
    public function __construct($currentPage, $pageCount, FACTFinder_ParametersParser $paramsParser)
    {
        $this->currentPage     = intval($currentPage);
        $this->pageCount       = intval($pageCount);
        $this->paramsParser    = $paramsParser;
        $this->params          = $paramsParser->getRequestParams();
    }

    /**
     * get iterator to iterate over all paging items around the current page, altogether not more than set
     * by "setDisplayPageCount" (default: 9). each item is an object of FACTFinder_Item
     *
     * @return Traversable
     */
    public function getIterator()
    {
        $iterator = new ArrayIterator();
        for($page = $this->getFirstPageNumberShown(); $page <= $this->getLastPageNumberShown(); $page++) {
            $iterator->append(
                FF::getInstance('item', $page, $this->getPageLink($page), ($page == $this->currentPage))
            );
        }
        return $iterator;
    }

    /**
     * returns the numer of all existing pages for the current result
     *
     * @return int pagecount
    **/
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * returns the current page number
     *
     * @return  int  pagenumber
    **/
    public function getCurrentPageNumber()
    {
        return $this->currentPage;
    }

    /**
     * returns the url (link) for the given page. If the page does not exist,
     * it returns an empty string
     *
     * @param   int     page number
     * @param   String  optional: link target
     * @return  String  url (link)
    **/
    public function getPageLink($page_number, $link_target = null)
    {
        if ($page_number > $this->pageCount || $page_number < 1) {
            return '';
        }
        $additionalParams = array('page' => $page_number);
        if ($this->sourceRefKey != null)
            $additionalParams['sourceRefKey'] = $this->sourceRefKey;
        return $this->paramsParser->createPageLink($this->params, $additionalParams, $link_target);
    }

    /**
     * returns the url (link) for the previous page
     *
     * @param   String  optional: link target
     * @return  String  url (link)
    **/
    public function getPreviousPageLink($link_target = '')
    {
        if ( $this->currentPage > 1) {
            $previous_page_number = ($this->currentPage - 1);
        } else {
            return '';
        }
        return $this->getPageLink($previous_page_number, $link_target);
    }

    /**
     * returns the url (link) for the next page
     *
     * @param   String  optional: link target
     * @return  String  url (link)
    **/
    public function getNextPageLink($link_target = '')
    {
        if ( $this->currentPage < $this->pageCount) {
            $previous_page_number = ($this->currentPage + 1);
        } else {
            return '';
        }
        return $this->getPageLink($previous_page_number, $link_target);
    }

    /**
     * set maximum count of pages to display
     *
     * @param int count of pages to display
     * @return void
     */
    public function setDisplayPageCount($displayPageCount)
    {
        $this->displayPageCount = intval($displayPageCount);
    }

    /**
     * returns the first page number for the pagelinks to be shown
     * needs the number of the maximum shown links
     *
     * @param   int  page links count
     * @return  int  first shown page number
    **/
    public function getFirstPageNumberShown()
    {
        if ($this->currentPage <= floor($this->displayPageCount / 2) || $this->pageCount < $this->displayPageCount) {
            return 1;
        } else if ($this->currentPage > ($this->pageCount - $this->displayPageCount +1 )) {
            return ($this->pageCount - $this->displayPageCount + 1);
        } else {
            return ($this->currentPage - floor($this->displayPageCount / 2));
        }
    }

    /**
     * returns the last page number for the pagelinks to be shown
     * needs the number of the maximum shown links
     *
     * @param   int  page links count
     * @return  int  first shown page number
    **/
    public function getLastPageNumberShown()
    {
        if ($this->pageCount < $this->displayPageCount) {
            return $this->pageCount;
        }

        $first_page_number = $this->getFirstPageNumberShown($this->displayPageCount);
        if ($first_page_number+$this->displayPageCount >= $this->pageCount) {
            return $this->pageCount;
        } else {
            return $first_page_number+$this->displayPageCount;
        }
    }

    /** Set the sourceRefKey to add to urls.
     * @param string sourceRefKey to set
     */
    public function setSourceRefKey($sourceRefKey)
    {
        $this->sourceRefKey = $sourceRefKey;
    }
}
