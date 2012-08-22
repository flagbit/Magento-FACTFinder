<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * represents a group in the ASN which contains several filters. By iterating over an ASN object, you will
 * get FACTFinder_AsnGroup objects in the loop.
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Asn.php 25893 2010-06-29 08:19:43Z rb $
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