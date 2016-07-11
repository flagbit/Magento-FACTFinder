<?php
namespace FACTFinder\Data;

/**
 * This is mostly just a collection of Record objects.
 */
class Result extends \ArrayIterator
{

    /**
     * @var int
     */
    private $foundRecordsCount;

    /**
     * @param Record[] $records The Record objects to add to the result.
     * @param int $foundRecordsCount Total number of records found for the
     *        search these records are from. This can be greater than
     *        count($records), because $records may just be the records from a
     *        single page, while $foundRecordsCount refers to all records found
     *        by the search.
     */
    public function __construct(
        array $records,
        $foundRecordsCount = 0
    ) {
        parent::__construct($records);
        $this->foundRecordsCount = (int)$foundRecordsCount;
    }

    /**
     * @return int
     */
    public function getFoundRecordsCount()
    {
        return $this->foundRecordsCount;
    }

}
