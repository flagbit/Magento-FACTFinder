<?php

/**
 * represents a group in the ASN which contains several filters
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @category  Collection
 * @package   FACTFinder\Common
 */
class FACTFinder_AsnGroup extends ArrayIterator
{
	private $name;
	private $detailedLinkCount;
	private $unit;
	private $hasPreviewImages = false;
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
		if ($filter instanceof FACTFinder_AsnFilterItem && $filter->hasPreviewImage()) {
			$this->hasPreviewImages = true;
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