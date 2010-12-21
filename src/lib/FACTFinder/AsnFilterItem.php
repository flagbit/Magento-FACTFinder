<?php

/**
 * represents an filterable item from the after search navigation
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: AsnFilterItem.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 */
class FACTFinder_AsnFilterItem extends FACTFinder_Item
{
	private $matchCount;
	private $clusterLevel;
	private $previewImage = null;
	private $field;

	public function __construct($value, $url, $isSelected = false, $matchCount = 0, $clusterLevel = 0, $previewImage = null, $field = ''){
		parent::__construct($value, $url, $isSelected);
		$this->matchCount = intval($matchCount);
		$this->clusterLevel = intval($clusterLevel);
		$this->previewImage = strval($previewImage);
		$this->field = strval($field);
	}

	/**
	 * @return String
	 */
	public function getField() {
		return $this->field;
	}
	
	/**
	 * @return int
	 */
	public function getMatchCount() {
		return $this->matchCount;
	}

	/**
	 * @return int
	 */
	public function getClusterLevel() {
		return $this->clusterLevel;
	}

	/**
	 * return url of a preview image or null if there is no set
	 *
	 * @return string image url or null if none exists
	 */
	public function getPreviewImage() {
		return $this->previewImage;
	}

	/**
	 * @return boolean
	 */
	public function hasPreviewImage() {
		return !empty($this->previewImage);
	}
}