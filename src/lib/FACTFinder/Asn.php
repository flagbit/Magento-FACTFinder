<?php

/**
 * represents a group in the ASN which contains several filters
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
 * @category  Collection
 * @package   FACTFinder\Common
 */
class FACTFinder_Asn extends ArrayIterator
{
	/**
	 * decorates the FACTFinder_AsnGroup::hasPreviewImages() method for each group in the asn. so if one group has
	 * preview images, this method returns true
	 *
	 * @return boolean
	 */
	public function hasPreviewImages()
	{
		$hasPreviewImages = false;
		foreach ($this AS $group) {
			if ($group->hasPreviewImages()) {
				$hasPreviewImages = true;
				break;
			}
		}
		return $hasPreviewImages;
	}
}