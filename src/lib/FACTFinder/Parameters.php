<?php

/**
 * the factfinder parameters contains all relevant parameter which makes effect to the factfinder search result.
 * so it represents a state of a factfinder result
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Parameters.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 */
class FACTFinder_Parameters
{
    private $query;
    private $channel;
    private $productsPerPage;
    private $page;
    private $filters;
    private $sortings;
    private $isNavigation;
	private $followSearch;

    /**
     * @param string query
     * @param string channel
     * @param int productsPerPage
     * @param int page
     * @param array filters
     * @param array sortings
     * @param boolean isNavigation
	 * @param int followSearch
     */
    public function __construct($query, $channel, $productsPerPage = null, $page = 1, array $filters = array(), array $sortings = array(), $isNavigation = false, $followSearch = 10000) {
        $this->query           = strval($query);
        $this->channel         = strval($channel);
        $this->productsPerPage = $productsPerPage == null ? null : intval($productsPerPage);
        $this->page            = intval($page);
        $this->filters         = $filters;
        $this->sortings        = $sortings;
        $this->isNavigation    = $isNavigation == true;
		$this->followSearch    = intval($followSearch);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getProductsPerPage()
    {
        return $this->productsPerPage;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return array string => string
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array string => string
     */
    public function getSortings()
    {
        return $this->sortings;
    }
    
    /**
     * @return boolean true if navigation is enabled
     */
    public function isNavigation()
    {
        return $this->isNavigation;
    }
	
    /**
     * @return int follow search value
     */
	public function getFollowSearch()
	{
		return $this->followSearch;
	}
}