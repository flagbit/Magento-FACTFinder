<?php
namespace FACTFinder\Data;

class BreadCrumbTrail extends \ArrayIterator
{
    /**
     * @param BreadCrumb[] $breadCrumbs
     */
    public function __construct (array $breadCrumbs)
    {
        parent::__construct($breadCrumbs);
    }
}
