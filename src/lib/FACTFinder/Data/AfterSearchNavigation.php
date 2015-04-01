<?php
namespace FACTFinder\Data;

/**
 * Represents the After Search Navigation (ASN) but it is really just an array
 * of FilterGroups.
 */
class AfterSearchNavigation extends \ArrayIterator
{
    /**
     * @param FilterGroup[] $filterGroups
     */
    public function __construct(array $filterGroups)
    {
        parent::__construct($filterGroups);
    }

    /**
     * @return bool
     */
    public function hasPreviewImages()
    {
        foreach ($this as $group)
            if ($group->hasPreviewImages())
                return true;

        return false;
    }
}
