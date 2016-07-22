<?php
namespace FACTFinder\Data;

class SortingItems extends \ArrayIterator
{
    /**
     * @param SortingItem[] $options Array of sorting option links.
     */
    public function __construct (array $options)
    {
        parent::__construct($options);
    }
}
