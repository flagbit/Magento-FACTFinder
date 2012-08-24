<?php
/**
 * FACT-Finder PHP Framework
 *
 * @category  Library
 * @package   FACTFinder\Common
 * @copyright Copyright (c) 2012 Omikron Data Quality GmbH (www.omikron.net)
 */

/**
 * this class represents a fact-finder search result. By iterating over a result object, you will get
 * FACTFinder_Record objects in the loop.
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id: Result.php 25893 2010-06-29 08:19:43Z rb $
 * @category  Collection
 * @package   FACTFinder\Common
 */
class FACTFinder_Result extends ArrayIterator
{
    private $foundRecordsCount;

    /**
     * @param array record (default: empty array)
     * @param int number of records factfinder found for the according query (default: 0)
     */
    public function __construct(array $records = array(), $foundRecordsCount = 0){
        parent::__construct($records);
        $this->foundRecordsCount = intval($foundRecordsCount);
    }

    /**
     * return number of records found in the whole FACT-Finder result.
     * this object only contains the records for the current page, that must not be the same count
     *
     * @return int found records count
     */
    public function getFoundRecordsCount(){
        return $this->foundRecordsCount;
    }
}