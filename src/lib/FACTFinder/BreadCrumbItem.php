<?php

/**
 * breadcrumb for the breadcrumb navigation
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Common
 */
class FACTFinder_BreadCrumbItem extends FACTFinder_Item
{
	const SEARCH_TYPE = 'search';
	const FILTER_TYPE = 'filter';
	
	private $type;
	private $fieldName;
	private $fieldUnit;
	
	/**
	 * @param string value
	 * @param string url
	 * @param boolean true if this breadcrumb represents the current state of the result
	 * @param string type should be "search" or "filter", otherwise the "is..." methods will not work (default: 'filter')
	 * @param string filtername
	 * @param string filter value
	 */
	public function __construct($value, $url, $isSelected = false, $type = 'filter', $fieldName = '', $fieldUnit = '')
	{
		parent::__construct($value, $url, $isSelected);
		$this->type = strval($type);
		if ($this->isFilter()) {
			$this->fieldName = strval($fieldName);
			$this->fieldUnit = strval($fieldUnit);
		}
	}
	
	/**
	 * @return boolean
	 */
	public function isFilter()
	{
		return $this->type == self::FILTER_TYPE;
	}
	
	/**
	 * @return boolean
	 */
	public function isSearch()
	{
		return $this->type == self::SEARCH_TYPE;
	}
	
	/**
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}
	
	/**
	 * @return string
	 */
	public function getFieldUnit()
	{
		return $this->fieldUnit;
	}
}