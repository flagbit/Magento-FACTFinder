<?php

/**
 * this class represents a fact-finder search result
 *
 * @author    Rudolf Batt <rb@omikron.net>
 * @version   $Id$
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