<?php
namespace FACTFinder\Data;

/**
 * Represents an item in a tag cloud.
 */
class TagQuery extends Item
{
    private $weight;
    private $searchCount;

    /**
     * @param string $query The search term - will be used as the item's label.
     * @param string $url
     * @param bool True, if this tag corresponds to the current search result.
     * @param float $weight Optional value between 0.0 and 1.0.
     * @param int $searchCount Indicates how often this query has been searched
     *        for.
     */
    public function __construct(
        $query,
        $url,
        $isSelected = false,
        $weight = 0.0,
        $searchCount = 0
    ) {
        parent::__construct($query, $url, $isSelected);
        $this->weight = (float)$weight;
        $this->searchCount = (int)$searchCount;
    }

    /**
     * @return float
     */
    public function getWeight() {
        return $this->weight;
    }

    /**
     * @return int
     */
    public function getSearchCount() {
        return $this->searchCount;
    }
}
