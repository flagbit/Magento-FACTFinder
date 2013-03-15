<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * represents a group in the ASN which contains several filters. By iterating over an AsnGroup object, you will
 * get AsnFilterItem objects in the loop.
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: AsnGroup.php 25893 2010-06-29 08:19:43Z rb $
 * @category  Collection
 * @package   FACTFinder\Common
 */
class FACTFinder_AsnGroup extends ArrayIterator
{
    private $name;
    private $detailedLinkCount;
    private $unit;
    private $hasPreviewImages = false;
    private $hasSelectedItems = false;
    private $isSliderStyle = false;

    /**
     * constructor
     *
     * @param array asn filters to add to this group (default: empty array)
     * @param string name of the group (default: empty string)
     * @param int number of detail links to show (default: 0)
     * @param string untit character of the group (default: empty string)
     * @param string style; possible values: DEFAULT|SLIDER|COLOR (default: DEFAULT)
     */
    public function __construct(array $asnFilters = array(), $name = '', $detailedLinkCount = 0, $unit = '', $style = 'DEFAULT') {
        $this->name = strval($name);
        $this->detailedLinkCount = intval($detailedLinkCount);
        $this->unit = strval($unit);
        $this->style = $style;

        parent::__construct();
        $this->addFilters($asnFilters);
    }

    /**
     * return true if group filtering should be done in slider style
     *
     * @return boolean
     */
    public function isSliderStyle() {
        return $this->style == 'SLIDER';
    }

    /**
     * @return boolean
     */
    public function isColorStyle() {
        return $this->style == 'COLOR';
    }
    
    /**
     * @return boolean
     */
    public function isMultiSelectStyle() {
        return $this->style == 'MULTISELECT';
    }
    
    /**
     * @return boolean
     */
    public function isTreeStyle() {
        return $this->style == 'TREE';
    }

    /**
     * @return boolean
     */
    public function isDefaultStyle() {
        return !$this->isSliderStyle() && !$this->isColorStyle();
    }

    /**
     * return boolean true if one or more items has a preview image
     */
    public function hasPreviewImages()
    {
        return $this->hasPreviewImages;
    }

    /**
     * return boolean true if one or more items are selected
     */
    public function hasSelectedItems()
    {
        return $this->hasSelectedItems;
    }

    /**
     * add every filter from the given array to this group
     *
     * @param array of filter objects
     * @return void
     */
    public function addFilters(array $filters)
    {
        foreach($filters AS $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @param filter object
     * @return void
     */
    public function addFilter($filter)
    {
        if ($filter instanceof FACTFinder_AsnFilterItem) {
            if ($filter->hasPreviewImage()) {
                $this->hasPreviewImages = true;
            } else if ($filter->isSelected()) {
                $this->hasSelectedItems = true;
            }
        }
        parent::append($filter);
    }

    /**
     * @return string name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string unit
     */
    public function getUnit() {
        return $this->unit;
    }

    /**
     * @return int
     */
    public function getDetailedLinkCount() {
        return $this->detailedLinkCount;
    }
}