<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * represents a query for suggest
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: SuggestQuery.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 */
class FACTFinder_SuggestQuery
{
    private $query;
    private $url;
    private $hitCount;
    private $type;
	private $imageUrl;

    /**
     * @param string $value query
	 * @param string $url url which uses the suggested query
     * @param string $hitCount number of products, which will be found with this query
     * @param string $type type of the query
	 * @param string imageUrl
     */
    public function __construct($query, $url, $hitCount = '', $type = '', $imageUrl = '') {
        $this->query    = strval($query);
        $this->url      = strval($url);
        $this->hitCount = $hitCount;
        $this->type     = $type;
		$this->imageUrl = $imageUrl;
    }

    /**
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * return int how many products will be found by this query
     */
    public function getHitCount() {
        return $this->hitCount;
    }

    /**
     * simple string which describes where this suggest query comes from (i.e. productname, category, logfile)
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

	/**
     * return image url, if one exists, otherwise returns empty string
     *
     * @return string
     */
    public function getImageUrl() {
        return $this->imageUrl;
    }
}