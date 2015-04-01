<?php
namespace FACTFinder\Data;

class Sorting extends \ArrayIterator
{
    /**
     * @param Item[] $options Array of sorting option links.
     */
    public function __construct (array $options)
    {
        parent::__construct($options);
    }
}
