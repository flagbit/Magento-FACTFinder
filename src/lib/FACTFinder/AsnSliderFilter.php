<?php

/**
 * represents an asn filter slider item
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: AsnSliderFilter.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 */
class FACTFinder_AsnSliderFilter
{
	private $absoluteMin;
	private $absoluteMax;
	private $selectedMin;
	private $selectedMax;
	private $baseUrl;
	private $field;

	/**
	 * @param string base url - it should be possible to simply append the selected min and max value
	 * @param float absolute min (default: 0.0)
	 * @param float absolute max (default: 0.0)
	 * @param float selected min (default: 0.0)
	 * @param float selected max (default: 0.0)
	 */
	public function __construct($baseUrl, $absoluteMin = 0, $absoluteMax = 0, $selectedMin = 0, $selectedMax = 0, $field = '') {
		$this->baseUrl = $baseUrl;
		$this->setAbsoluteRange($absoluteMin, $absoluteMax);
		$this->setSelectedRange($selectedMin, $selectedMax);
		$this->field = strval($field);
	}

	/**
	 * @param float absolute min
	 * @param float absolute max
	 */
	private function setAbsoluteRange($min, $max) {
		$this->absoluteMin = floatval($min);
		$this->absoluteMax = floatval($max);
	}

	/**
	 * @param float selected min
	 * @param float selected max
	 */
	public function setSelectedRange($min, $max) {
		$this->selectedMin = floatval($min);
		$this->selectedMax = floatval($max);
	}

	/**
	 * return float
	 */
	public function getAbsoluteMin() {
		return $this->absoluteMin;
	}

	/**
	 * return float
	 */
	public function getAbsoluteMax() {
		return $this->absoluteMax;
	}

	/**
	 * return float
	 */
	public function getSelectedMin() {
		return $this->selectedMin;
	}

	/**
	 * return float
	 */
	public function getSelectedMax() {
		return $this->selectedMax;
	}

	/**
	 * @return string url for the current selected range
	 */
	public function getUrl() {
		return $this->baseUrl . $this->selectedMin . ' - ' . $this->selectedMax;
	}

	/**
	 * @return string base url
	 */
	public function getBaseUrl() {
		return $this->baseUrl;
	}

	/**
	 * returns true if the selected range is not the same as the absolute range
	 *
	 * @return boolean
	 */
	public function isSelected() {
		return $this->selectedMin != $this->absoluteMin || $this->selectedMax != $this->absoluteMax;
	}
}