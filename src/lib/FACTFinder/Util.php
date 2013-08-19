<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * util class for some repeated issues which do not fit to a single class
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Util.php 25893 2010-06-29 08:19:43Z rb $
 * @package   FACTFinder\Common
 **/
class FACTFinder_Util
{
    protected $searchAdapter;
    protected $ffparams;

    public function __construct(FACTFinder_Parameters $ffparams, FACTFinder_Default_SearchAdapter $searchAdapter) {
        $this->ffparams = $ffparams;
        $this->searchAdapter = $searchAdapter;
    }

    public function createJavaScriptClickCode($record, $title, $sid, $useLegacyTracking = true) {
        if (strlen($sid) == 0) $sid = session_id();
        if ($useLegacyTracking) {
            return $this->createLegacyJavaScriptClickCode($record, $title, $sid);
        } else {
            $channel = $this->ffparams->getChannel();
            $id = $record->getId();
            return $this->createJavaScriptTrackingCode('inspect', $this->searchAdapter->getResult()->getRefKey(), $sid, $channel, array("id" => $id));
        }
    }

    public function createJavaScriptTrackingCode($event, $sourceRefKey, $sid = null, $channel = null, $extraParams = array()) {
        if (strlen($sid) == 0) $sid = session_id();
        if (strlen($channel) == 0) $channel = $this->ffparams->getChannel();
        $sid = addslashes($sid);
        $extString = '{';
        foreach ($extraParams AS $extKey => $extVal) {
            $extString .= $extKey . ": '" . $extVal . '\'';
        }
        $extString .= '}';
        return "trackEvent('$event', '$sourceRefKey', '$sid', '$channel', $extString);";
    }

    public function createJavaScriptEventCode($trackingEvents, $sid, $channel) {
        $result = "";
        $sourceRefKey = $this->searchAdapter->getResult()->getRefKey();
        foreach ($trackingEvents AS $event => $extraParams) {
            $result .= $this->createJavaScriptTrackingCode($event, $sourceRefKey, $sid, $channel, $extraParams);
        }
        return "$(document).ready( function (){ $result });";
    }

    /**
     * @return string javascript method call "clickProduct" with all needed arguments
     */
    private function createLegacyJavaScriptClickCode($record, $title, $sid)
    {
        $query             = addcslashes(htmlspecialchars($this->ffparams->getQuery()), "'");
        $channel           = $this->ffparams->getChannel();

        $currentPageNumber = $this->searchAdapter->getPaging()->getCurrentPageNumber();
        $origPageSize      = $this->searchAdapter->getProductsPerPageOptions()->getDefaultOption()->getValue();
        $pageSize          = $this->searchAdapter->getProductsPerPageOptions()->getSelectedOption()->getValue();

        $position          = $record->getPosition();
        if ($position != 0 && $query != '') {
            $originalPosition  = $record->getOriginalPosition();
            if (!$originalPosition) $originalPosition = $position;

            $similarity        = number_format($record->getSimilarity(), 2, '.', '');
            $id                = $record->getId();

            $title             = addslashes($title);
            $sid               = addslashes($sid);
            $clickCode         = "clickProduct('$query', '$id', '$position', '$originalPosition', '$currentPageNumber',"
                                ."'$similarity', '$sid', '$title', '$pageSize', '$origPageSize', '$channel', 'click');";
        } else {
            $clickCode = '';
        }

        return $clickCode;
    }
}
