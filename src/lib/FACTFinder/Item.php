<?php

/**
 * an factfinder item is a simple selectable item on a website, so it is represented by a value and an url
 * it is NOT defined yet, how this item affects on the website
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @package   FACTFinder\Common
**/
class FACTFinder_Item
{
	private $value;
	private $url;
	private $isSelected;
	
	/**
	 * @param string value
	 * @param string url
	 * @param boolean is selected (default: false)
	 */
	public function __construct($value, $url, $isSelected = false){
		$this->value = strval($value);
		$this->url = strval($url);
		$this->isSelected = $isSelected == true;
	}
	
	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * @return boolean
	 */
	public function isSelected() {
		return $this->isSelected;
	}
}