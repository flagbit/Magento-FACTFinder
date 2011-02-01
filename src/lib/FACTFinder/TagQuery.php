<?php

/**
 * represents a tag item for the tagcloud
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Common
 */
class FACTFinder_TagQuery extends FACTFinder_Item
{
    private $weight;
    private $searchCount;

    /**
     * @param string $value query
     * @param string $url
     * @param boolean true if this tag lead to the current search result
     * @param double $weight value between 0.0 and 1.0 (optional - default 0.0)
     * @param int $searchCount how often this query was searched in the last 7 days (optional - default 0)
     */
    public function __construct($value, $url, $isSelected = false, $weight = 0.0, $searchCount = 0) {
        parent::__construct($value, $url, $isSelected);
        $this->weight = floatval($weight);
        $this->searchCount = intval($searchCount);
    }
    
    /**
     * @return double value between 0.0 and 1.0 to calculate the importance of the query
     */
    public function getWeight() {
        return $this->weight;
    }

    /**
     * @return int how often this query was searched in the last 7 days
     */
    public function getSearchCount() {
        return $this->searchCount;
    }
}